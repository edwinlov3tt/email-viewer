"""
Email Viewer Flask Application
Handles parsing of .eml and .msg files with full support for attachments
"""

from flask import Flask, request, jsonify, send_file, send_from_directory
from flask_cors import CORS
import extract_msg
import email
from email import policy
from bs4 import BeautifulSoup
import os
import tempfile
import uuid
from pathlib import Path
from datetime import datetime
import base64
import re
import zipfile
import io

# Configure Flask to serve static files from current directory
app = Flask(__name__, static_folder='.', static_url_path='')
CORS(app)

# Configuration
UPLOAD_FOLDER = 'uploads'
MAX_FILE_SIZE = 50 * 1024 * 1024  # 50MB
ALLOWED_EXTENSIONS = {'.eml', '.msg'}

# Create uploads directory
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# Store parsed emails in memory (in production, use Redis or database)
email_storage = {}


def sanitize_html(html_content):
    """Sanitize HTML content using BeautifulSoup"""
    if not html_content:
        return ''

    # Use html.parser instead of lxml (built-in, no dependencies)
    soup = BeautifulSoup(html_content, 'html.parser')

    # Remove script and style tags
    for script in soup(['script', 'style']):
        script.decompose()

    # Make all links open in new tab
    for link in soup.find_all('a'):
        link['target'] = '_blank'
        link['rel'] = 'noopener noreferrer'  # Security best practice

    return str(soup)


def parse_eml_file(file_path):
    """Parse .eml file using Python's built-in email library"""
    with open(file_path, 'rb') as f:
        msg = email.message_from_binary_file(f, policy=policy.default)

    # Extract headers
    headers = {
        'from': msg.get('From', ''),
        'to': msg.get('To', ''),
        'cc': msg.get('Cc', ''),
        'bcc': msg.get('Bcc', ''),
        'subject': msg.get('Subject', ''),
        'date': msg.get('Date', ''),
        'message_id': msg.get('Message-ID', ''),
        'reply_to': msg.get('Reply-To', '')
    }

    # Extract body
    body_text = ''
    body_html = ''

    if msg.is_multipart():
        for part in msg.walk():
            content_type = part.get_content_type()

            if content_type == 'text/plain' and not body_text:
                try:
                    body_text = part.get_content()
                except:
                    body_text = str(part.get_payload(decode=True), errors='ignore')

            elif content_type == 'text/html' and not body_html:
                try:
                    body_html = part.get_content()
                except:
                    body_html = str(part.get_payload(decode=True), errors='ignore')
    else:
        content_type = msg.get_content_type()
        if content_type == 'text/plain':
            body_text = msg.get_content()
        elif content_type == 'text/html':
            body_html = msg.get_content()

    # Extract attachments first (needed for CID resolution)
    attachments = []
    if msg.is_multipart():
        for part in msg.walk():
            content_disposition = part.get_content_disposition()

            # Check for both attachments and inline content (like embedded images)
            if content_disposition in ('attachment', 'inline'):
                filename = part.get_filename() or 'attachment'
                attachment_id = str(uuid.uuid4())
                attachment_path = os.path.join(UPLOAD_FOLDER, f"{attachment_id}_{filename}")

                # Get attachment data
                attachment_data = part.get_payload(decode=True)

                # Save attachment
                with open(attachment_path, 'wb') as f:
                    f.write(attachment_data)

                # Extract Content-ID for inline images
                content_id = part.get('Content-ID', '')
                if content_id:
                    # Remove < > brackets if present
                    content_id = content_id.strip('<>')

                attachments.append({
                    'id': attachment_id,
                    'filename': filename,
                    'size': os.path.getsize(attachment_path),
                    'content_type': part.get_content_type(),
                    'path': attachment_path,
                    'content_id': content_id,
                    'data': attachment_data  # Store for inline image conversion
                })

    # Sanitize HTML and resolve CID images
    if body_html:
        body_html = sanitize_html(body_html)
        body_html = resolve_cid_images(body_html, attachments)
    else:
        # Convert plain text to HTML
        body_html = f'<pre>{body_text}</pre>' if body_text else ''

    # Remove binary data from attachments before returning (not JSON serializable)
    for attachment in attachments:
        attachment.pop('data', None)

    return {
        'headers': headers,
        'body': {
            'html': body_html,
            'text': body_text
        },
        'attachments': attachments
    }


def sanitize_filename(filename):
    """Sanitize filename by removing null bytes and invalid characters"""
    if not filename:
        return 'attachment'

    # Remove null bytes
    filename = filename.replace('\x00', '')

    # Remove or replace other problematic characters
    filename = re.sub(r'[<>:"/\\|?*]', '_', filename)

    # Ensure filename is not empty after sanitization
    if not filename or filename.strip() == '':
        return 'attachment'

    return filename.strip()


def resolve_cid_images(html_content, attachments):
    """Replace cid: image references with base64 data URLs"""
    if not html_content or not attachments:
        return html_content

    # Create a mapping of Content-ID to attachment data
    cid_map = {}
    for attachment in attachments:
        content_id = attachment.get('content_id', '')
        if content_id and attachment.get('data'):
            cid_map[content_id] = {
                'data': attachment['data'],
                'content_type': attachment['content_type']
            }

    # Replace cid: references in the HTML
    def replace_cid(match):
        cid = match.group(1)
        if cid in cid_map:
            # Convert to base64 data URL
            attachment_info = cid_map[cid]
            base64_data = base64.b64encode(attachment_info['data']).decode('utf-8')
            data_url = f"data:{attachment_info['content_type']};base64,{base64_data}"
            return f'src="{data_url}"'
        return match.group(0)  # Return original if CID not found

    # Find and replace all cid: references
    html_content = re.sub(r'src=["\']cid:([^"\']+)["\']', replace_cid, html_content, flags=re.IGNORECASE)

    return html_content


def parse_msg_file(file_path):
    """Parse .msg file using extract-msg library"""
    msg = extract_msg.Message(file_path)

    # Extract headers - using safe getattr with defaults
    headers = {
        'from': getattr(msg, 'sender', '') or '',
        'to': getattr(msg, 'to', '') or '',
        'cc': getattr(msg, 'cc', '') or '',
        'bcc': getattr(msg, 'bcc', '') or '',
        'subject': getattr(msg, 'subject', '') or '',
        'date': str(getattr(msg, 'date', '')) if getattr(msg, 'date', None) else '',
        'message_id': getattr(msg, 'messageId', '') or getattr(msg, 'message_id', '') or '',
        'reply_to': getattr(msg, 'replyTo', '') or ''
    }

    # Extract body
    body_text = getattr(msg, 'body', '') or ''
    body_html = getattr(msg, 'htmlBody', '') or ''

    # Extract attachments first (needed for CID resolution)
    attachments = []
    try:
        for attachment in msg.attachments:
            try:
                attachment_id = str(uuid.uuid4())

                # Get filename and sanitize it
                raw_filename = getattr(attachment, 'longFilename', None) or getattr(attachment, 'shortFilename', None) or 'attachment'
                filename = sanitize_filename(raw_filename)

                # Sanitize for file path
                safe_filename = sanitize_filename(filename)
                attachment_path = os.path.join(UPLOAD_FOLDER, f"{attachment_id}_{safe_filename}")

                # Get attachment data
                attachment_data = getattr(attachment, 'data', None)
                if attachment_data:
                    # Save attachment
                    with open(attachment_path, 'wb') as f:
                        f.write(attachment_data)

                    # Try to get Content-ID for inline images
                    content_id = getattr(attachment, 'cid', '') or getattr(attachment, 'contentId', '') or ''
                    if content_id:
                        # Remove < > brackets if present
                        content_id = content_id.strip('<>')

                    attachments.append({
                        'id': attachment_id,
                        'filename': filename,  # Use original (but sanitized) filename for display
                        'size': len(attachment_data),
                        'content_type': getattr(attachment, 'mimetype', 'application/octet-stream') or 'application/octet-stream',
                        'path': attachment_path,
                        'content_id': content_id,
                        'data': attachment_data  # Store for inline image conversion
                    })
            except Exception as e:
                # Skip this attachment if there's an error
                print(f"Error processing attachment: {e}")
                continue
    except Exception as e:
        print(f"Error processing attachments: {e}")

    msg.close()

    # Sanitize HTML and resolve CID images
    if body_html:
        body_html = sanitize_html(body_html)
        body_html = resolve_cid_images(body_html, attachments)
    else:
        # Convert plain text to HTML
        body_html = f'<pre>{body_text}</pre>' if body_text else ''

    # Remove binary data from attachments before returning (not JSON serializable)
    for attachment in attachments:
        attachment.pop('data', None)

    return {
        'headers': headers,
        'body': {
            'html': body_html,
            'text': body_text
        },
        'attachments': attachments
    }


@app.route('/api/parse-email', methods=['POST'])
def parse_email():
    """Main endpoint for parsing email files"""
    try:
        # Check if file was uploaded
        if 'email' not in request.files:
            return jsonify({'success': False, 'error': 'No file uploaded'}), 400

        file = request.files['email']

        if file.filename == '':
            return jsonify({'success': False, 'error': 'No file selected'}), 400

        # Validate file extension
        file_ext = Path(file.filename).suffix.lower()
        if file_ext not in ALLOWED_EXTENSIONS:
            return jsonify({'success': False, 'error': f'Invalid file type: {file_ext}'}), 400

        # Save uploaded file temporarily
        email_id = str(uuid.uuid4())
        temp_path = os.path.join(UPLOAD_FOLDER, f"{email_id}_{file.filename}")
        file.save(temp_path)

        # Check file size
        if os.path.getsize(temp_path) > MAX_FILE_SIZE:
            os.remove(temp_path)
            return jsonify({'success': False, 'error': 'File size exceeds 50MB limit'}), 400

        # Parse email based on file type
        try:
            if file_ext == '.eml':
                email_data = parse_eml_file(temp_path)
            elif file_ext == '.msg':
                email_data = parse_msg_file(temp_path)
            else:
                raise ValueError(f'Unsupported file type: {file_ext}')

            # Add metadata
            email_data['id'] = email_id
            email_data['filename'] = file.filename
            email_data['temp_path'] = temp_path

            # Store in memory
            email_storage[email_id] = email_data

            return jsonify({
                'success': True,
                'data': email_data
            })

        except Exception as e:
            # Clean up on error
            if os.path.exists(temp_path):
                os.remove(temp_path)
            raise e

    except Exception as e:
        return jsonify({
            'success': False,
            'error': f'Failed to parse email: {str(e)}'
        }), 500


@app.route('/api/download-attachment', methods=['GET'])
def download_attachment():
    """Download an attachment"""
    try:
        email_id = request.args.get('email_id')
        attachment_id = request.args.get('attachment_id')

        if not email_id or not attachment_id:
            return jsonify({'success': False, 'error': 'Missing parameters'}), 400

        # Find attachment
        email_data = email_storage.get(email_id)
        if not email_data:
            return jsonify({'success': False, 'error': 'Email not found'}), 404

        attachment = next(
            (att for att in email_data['attachments'] if att['id'] == attachment_id),
            None
        )

        if not attachment:
            return jsonify({'success': False, 'error': 'Attachment not found'}), 404

        return send_file(
            attachment['path'],
            as_attachment=True,
            download_name=attachment['filename']
        )

    except Exception as e:
        return jsonify({
            'success': False,
            'error': f'Failed to download attachment: {str(e)}'
        }), 500


@app.route('/api/download-images-zip', methods=['GET'])
def download_images_zip():
    """Download all images from an email as a ZIP file"""
    try:
        email_id = request.args.get('email_id')

        if not email_id:
            return jsonify({'success': False, 'error': 'Missing email_id parameter'}), 400

        email_data = email_storage.get(email_id)
        if not email_data:
            return jsonify({'success': False, 'error': 'Email not found'}), 404

        # Filter image attachments
        images = [
            att for att in email_data['attachments']
            if att.get('content_type', '').startswith('image/')
        ]

        if not images:
            return jsonify({'success': False, 'error': 'No images found in email'}), 404

        # Create ZIP file in memory
        zip_buffer = io.BytesIO()
        with zipfile.ZipFile(zip_buffer, 'w', zipfile.ZIP_DEFLATED) as zf:
            for img in images:
                if os.path.exists(img['path']):
                    zf.write(img['path'], img['filename'])

        zip_buffer.seek(0)

        # Generate filename from email subject or use default
        subject = email_data.get('headers', {}).get('subject', 'email')
        safe_subject = re.sub(r'[^\w\s-]', '', subject)[:30].strip() or 'email'
        zip_filename = f"{safe_subject}_images.zip"

        return send_file(
            zip_buffer,
            mimetype='application/zip',
            as_attachment=True,
            download_name=zip_filename
        )

    except Exception as e:
        return jsonify({
            'success': False,
            'error': f'Failed to create images ZIP: {str(e)}'
        }), 500


@app.route('/api/parse-nested-email', methods=['POST'])
def parse_nested_email():
    """Parse a nested email attachment"""
    try:
        email_id = request.form.get('email_id')
        attachment_id = request.form.get('attachment_id')

        if not email_id or not attachment_id:
            return jsonify({'success': False, 'error': 'Missing parameters'}), 400

        # Find the parent email
        email_data = email_storage.get(email_id)
        if not email_data:
            return jsonify({'success': False, 'error': 'Email not found'}), 404

        # Find the attachment
        attachment = next(
            (att for att in email_data['attachments'] if att['id'] == attachment_id),
            None
        )

        if not attachment:
            return jsonify({'success': False, 'error': 'Attachment not found'}), 404

        # Parse the nested email based on file type
        file_ext = Path(attachment['filename']).suffix.lower()

        if file_ext == '.eml':
            nested_email_data = parse_eml_file(attachment['path'])
        elif file_ext == '.msg':
            nested_email_data = parse_msg_file(attachment['path'])
        else:
            return jsonify({'success': False, 'error': 'Not an email file'}), 400

        # Generate new ID for nested email
        nested_email_id = str(uuid.uuid4())
        nested_email_data['id'] = nested_email_id
        nested_email_data['filename'] = attachment['filename']
        nested_email_data['is_nested'] = True
        nested_email_data['parent_email_id'] = email_id

        # Store the nested email
        email_storage[nested_email_id] = nested_email_data

        return jsonify({
            'success': True,
            'data': nested_email_data
        })

    except Exception as e:
        return jsonify({
            'success': False,
            'error': f'Failed to parse nested email: {str(e)}'
        }), 500


@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({'status': 'healthy', 'service': 'email-viewer'})


@app.route('/')
def index():
    """Serve the main index.html file"""
    return send_from_directory('.', 'index.html')


@app.route('/js/<path:filename>')
def serve_js(filename):
    """Serve JavaScript files"""
    return send_from_directory('js', filename)


if __name__ == '__main__':
    # Get port from environment variable or default to 5000 for local dev
    port = int(os.environ.get('PORT', 5000))
    # Only use debug mode in local development
    debug = os.environ.get('FLASK_ENV') != 'production'
    app.run(host='0.0.0.0', port=port, debug=debug)
