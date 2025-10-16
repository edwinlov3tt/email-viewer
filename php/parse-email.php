<?php
/**
 * Email Parser Endpoint
 *
 * Handles parsing of .eml and .msg files and returns structured email data
 */

require_once 'config.php';

use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message;
use Hfig\MAPI;
use Hfig\MAPI\OLE\Pear;

// Set JSON response header
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['email']) || $_FILES['email']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

// Validate the uploaded file
$validation = validateUploadedFile($_FILES['email']);
if (!$validation['success']) {
    echo json_encode($validation);
    exit;
}

// Clean old files periodically
if (rand(1, 100) <= 5) { // 5% chance to clean on each request
    cleanOldFiles();
}

try {
    $uploadedFile = $_FILES['email'];
    $originalName = sanitizeFilename($uploadedFile['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $uploadId = generateUploadId();

    // Create temporary file path
    $tempPath = UPLOAD_DIR . $uploadId . '_' . $originalName;

    // Move uploaded file to temporary location
    if (!move_uploaded_file($uploadedFile['tmp_name'], $tempPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Parse email based on file type
    $emailData = null;

    if ($extension === 'eml') {
        $emailData = parseEmlFile($tempPath);
    } elseif ($extension === 'msg') {
        $emailData = parseMsgFile($tempPath);
    } else {
        throw new Exception('Unsupported file type');
    }

    // Store in session for attachment access
    if (!isset($_SESSION['emails'])) {
        $_SESSION['emails'] = [];
    }

    $emailData['id'] = $uploadId;
    $emailData['filename'] = $originalName;
    $emailData['temp_path'] = $tempPath;

    $_SESSION['emails'][$uploadId] = [
        'path' => $tempPath,
        'data' => $emailData,
        'timestamp' => time()
    ];

    // Clean up old session data
    foreach ($_SESSION['emails'] as $id => $email) {
        if (time() - $email['timestamp'] > SESSION_TIMEOUT) {
            if (file_exists($email['path'])) {
                unlink($email['path']);
            }
            unset($_SESSION['emails'][$id]);
        }
    }

    // Return parsed email data
    echo json_encode([
        'success' => true,
        'data' => $emailData
    ]);

} catch (Exception $e) {
    // Clean up temporary file if exists
    if (isset($tempPath) && file_exists($tempPath)) {
        unlink($tempPath);
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to parse email: ' . $e->getMessage()
    ]);
}

/**
 * Parse .eml file using ZBateson MailMimeParser
 */
function parseEmlFile($filePath) {
    $parser = new MailMimeParser();
    $message = $parser->parse(file_get_contents($filePath), true);

    // Extract headers
    $headers = [
        'from' => cleanEmailAddress($message->getHeaderValue('from')),
        'to' => cleanEmailAddress($message->getHeaderValue('to')),
        'cc' => cleanEmailAddress($message->getHeaderValue('cc')),
        'bcc' => cleanEmailAddress($message->getHeaderValue('bcc')),
        'subject' => $message->getHeaderValue('subject'),
        'date' => $message->getHeaderValue('date'),
        'message_id' => $message->getHeaderValue('message-id'),
        'reply_to' => cleanEmailAddress($message->getHeaderValue('reply-to'))
    ];

    // Extract body
    $bodyHtml = $message->getHtmlContent();
    $bodyText = $message->getTextContent();

    // Sanitize HTML content
    if ($bodyHtml) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        $purifier = new HTMLPurifier($config);
        $bodyHtml = $purifier->purify($bodyHtml);
    }

    // Extract attachments
    $attachments = [];
    $attachmentParts = $message->getAllAttachmentParts();

    foreach ($attachmentParts as $attachment) {
        $attachmentId = uniqid('att_');
        $filename = $attachment->getFilename() ?: 'attachment';
        $attachmentPath = UPLOAD_DIR . $attachmentId . '_' . $filename;

        // Save attachment to temporary location
        $stream = $attachment->getContentStream();
        if ($stream && file_put_contents($attachmentPath, $stream)) {
            $attachments[] = [
                'id' => $attachmentId,
                'filename' => $filename,
                'size' => filesize($attachmentPath),
                'content_type' => $attachment->getContentType(),
                'path' => $attachmentPath
            ];
        }
    }

    // Get all headers as array
    $rawHeaders = [];
    foreach ($message->getAllHeaders() as $header) {
        $rawHeaders[$header->getName()] = $header->getValue();
    }

    return [
        'headers' => $headers,
        'body' => [
            'html' => $bodyHtml,
            'text' => $bodyText
        ],
        'attachments' => $attachments,
        'raw_headers' => $rawHeaders
    ];
}

/**
 * Parse .msg file using MAPI
 */
function parseMsgFile($filePath) {
    try {
        // Create factories for message parsing and OLE document handling
        $messageFactory = new MAPI\MapiMessageFactory();
        $documentFactory = new Pear\DocumentFactory();

        // Create an OLE document from the .msg file
        $ole = $documentFactory->createFromFile($filePath);

        if (!$ole) {
            throw new Exception('Failed to read MSG file');
        }

        // Parse the OLE document into a MAPI message object
        $message = $messageFactory->parseMessage($ole);

        if (!$message) {
            throw new Exception('Failed to parse MSG message');
        }

        // Extract headers using proper methods
        $sender = $message->getSender();
        $senderStr = $sender ? (string)$sender : '';

        // Get recipients
        $toRecipients = [];
        $ccRecipients = [];
        $bccRecipients = [];

        foreach ($message->getRecipients() as $recipient) {
            $recipientStr = (string)$recipient;
            $recipientType = $recipient->getType();

            if ($recipientType === 'to') {
                $toRecipients[] = $recipientStr;
            } elseif ($recipientType === 'cc') {
                $ccRecipients[] = $recipientStr;
            } elseif ($recipientType === 'bcc') {
                $bccRecipients[] = $recipientStr;
            }
        }

        $headers = [
            'from' => cleanEmailAddress($senderStr),
            'to' => cleanEmailAddress(implode(', ', $toRecipients)),
            'cc' => cleanEmailAddress(implode(', ', $ccRecipients)),
            'bcc' => cleanEmailAddress(implode(', ', $bccRecipients)),
            'subject' => $message->properties['subject'] ?? '',
            'date' => formatDate($message->properties['client_submit_time'] ?? $message->properties['message_delivery_time'] ?? ''),
            'message_id' => $message->properties['internet_message_id'] ?? '',
            'reply_to' => ''
        ];

        // Extract body
        $bodyText = $message->getBody() ?? '';
        $bodyHtml = $message->getBodyHTML() ?? '';

        // Sanitize HTML content
        if ($bodyHtml) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.SafeIframe', true);
            $purifier = new HTMLPurifier($config);
            $bodyHtml = $purifier->purify($bodyHtml);
        }

        // Extract attachments
        $attachments = [];
        foreach ($message->getAttachments() as $attachment) {
            $attachmentId = uniqid('att_');
            $filename = $attachment->getFilename() ?? 'attachment';
            $attachmentPath = UPLOAD_DIR . $attachmentId . '_' . sanitizeFilename($filename);

            // Save attachment data
            $attachmentData = $attachment->getData();
            if ($attachmentData && file_put_contents($attachmentPath, $attachmentData)) {
                $attachments[] = [
                    'id' => $attachmentId,
                    'filename' => $filename,
                    'size' => filesize($attachmentPath),
                    'content_type' => $attachment->getMimeType() ?? 'application/octet-stream',
                    'path' => $attachmentPath
                ];
            }
        }

        return [
            'headers' => $headers,
            'body' => [
                'html' => $bodyHtml,
                'text' => $bodyText
            ],
            'attachments' => $attachments
        ];

    } catch (Exception $e) {
        // Fallback: Try alternative MSG parsing method
        return parseMsgAlternative($filePath);
    }
}

/**
 * Alternative MSG parsing method (fallback)
 */
function parseMsgAlternative($filePath) {
    // This is a simplified fallback - in production you'd use a more robust library
    $content = file_get_contents($filePath);

    // Extract basic information using regex patterns
    $headers = [
        'from' => extractPattern($content, '/From:\s*(.+?)[\r\n]/i'),
        'to' => extractPattern($content, '/To:\s*(.+?)[\r\n]/i'),
        'subject' => extractPattern($content, '/Subject:\s*(.+?)[\r\n]/i'),
        'date' => extractPattern($content, '/Date:\s*(.+?)[\r\n]/i'),
        'cc' => '',
        'bcc' => '',
        'message_id' => '',
        'reply_to' => ''
    ];

    // Try to extract body
    $bodyText = 'MSG file parsing requires full library support. Basic headers extracted.';
    $bodyHtml = '<p>' . htmlspecialchars($bodyText) . '</p>';

    return [
        'headers' => $headers,
        'body' => [
            'html' => $bodyHtml,
            'text' => $bodyText
        ],
        'attachments' => []
    ];
}

/**
 * Clean email address string
 */
function cleanEmailAddress($address) {
    if (empty($address)) return '';

    // Remove extra whitespace
    $address = trim($address);

    // Handle multiple addresses
    if (strpos($address, ',') !== false) {
        $addresses = explode(',', $address);
        $addresses = array_map('trim', $addresses);
        return implode(', ', $addresses);
    }

    return $address;
}

/**
 * Format date string
 */
function formatDate($date) {
    if (empty($date)) return '';

    try {
        if (is_numeric($date)) {
            $timestamp = $date;
        } else {
            $timestamp = strtotime($date);
        }

        if ($timestamp) {
            return date('Y-m-d H:i:s', $timestamp);
        }
    } catch (Exception $e) {
        // Return original if parsing fails
    }

    return $date;
}

/**
 * Extract pattern from content
 */
function extractPattern($content, $pattern) {
    if (preg_match($pattern, $content, $matches)) {
        return trim($matches[1]);
    }
    return '';
}