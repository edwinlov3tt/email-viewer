# API Routes

## Overview

Flask-based REST API for email parsing and attachment handling.

## Base URL

- **Development**: `http://localhost:5000/api`
- **Production**: `https://[railway-app].up.railway.app/api`

## Authentication

No authentication required. API is open.

## Endpoints

### Health Check

```
GET /health
```

**Response**
```json
{
  "status": "healthy",
  "service": "email-viewer"
}
```

**Location**: `app.py:449`

---

### Parse Email

```
POST /api/parse-email
```

Parse an uploaded .eml or .msg email file.

**Request**
- Content-Type: `multipart/form-data`
- Body: Form data with `email` field containing the file

**Request Example**
```javascript
const formData = new FormData();
formData.append('email', file);

fetch('/api/parse-email', {
  method: 'POST',
  body: formData
});
```

**Response (200 OK)**
```json
{
  "success": true,
  "data": {
    "id": "uuid-string",
    "filename": "email.msg",
    "temp_path": "uploads/uuid_email.msg",
    "headers": {
      "from": "sender@example.com",
      "to": "recipient@example.com",
      "cc": "",
      "bcc": "",
      "subject": "Email Subject",
      "date": "Mon, 16 Oct 2024 10:30:00 -0400",
      "message_id": "<msgid@example.com>",
      "reply_to": ""
    },
    "body": {
      "html": "<html>Email content...</html>",
      "text": "Plain text version..."
    },
    "attachments": [
      {
        "id": "uuid-string",
        "filename": "document.pdf",
        "size": 12345,
        "content_type": "application/pdf",
        "path": "uploads/uuid_document.pdf",
        "content_id": ""
      }
    ]
  }
}
```

**Errors**

| Code | Condition | Response |
|------|-----------|----------|
| 400 | No file uploaded | `{"success": false, "error": "No file uploaded"}` |
| 400 | No file selected | `{"success": false, "error": "No file selected"}` |
| 400 | Invalid file type | `{"success": false, "error": "Invalid file type: .txt"}` |
| 400 | File too large | `{"success": false, "error": "File size exceeds 10MB limit"}` |
| 500 | Parse error | `{"success": false, "error": "Failed to parse email: ..."}` |

**Location**: `app.py:294`

---

### Download Attachment

```
GET /api/download-attachment
```

Download an attachment from a parsed email.

**Query Parameters**

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `email_id` | string | Yes | ID of the parsed email |
| `attachment_id` | string | Yes | ID of the attachment |

**Request Example**
```
GET /api/download-attachment?email_id=abc123&attachment_id=def456
```

**Response (200 OK)**
- File download with original filename
- Content-Disposition: attachment

**Errors**

| Code | Condition | Response |
|------|-----------|----------|
| 400 | Missing parameters | `{"success": false, "error": "Missing parameters"}` |
| 404 | Email not found | `{"success": false, "error": "Email not found"}` |
| 404 | Attachment not found | `{"success": false, "error": "Attachment not found"}` |
| 500 | Download error | `{"success": false, "error": "Failed to download attachment: ..."}` |

**Location**: `app.py:357`

---

### Parse Nested Email

```
POST /api/parse-nested-email
```

Parse an email attachment (.eml or .msg) from a previously parsed email.

**Request**
- Content-Type: `multipart/form-data`
- Body: Form data with `email_id` and `attachment_id`

**Request Example**
```javascript
const formData = new FormData();
formData.append('email_id', 'abc123');
formData.append('attachment_id', 'def456');

fetch('/api/parse-nested-email', {
  method: 'POST',
  body: formData
});
```

**Response (200 OK)**
```json
{
  "success": true,
  "data": {
    "id": "uuid-string",
    "filename": "forwarded.eml",
    "is_nested": true,
    "parent_email_id": "abc123",
    "headers": { ... },
    "body": { ... },
    "attachments": [ ... ]
  }
}
```

**Errors**

| Code | Condition | Response |
|------|-----------|----------|
| 400 | Missing parameters | `{"success": false, "error": "Missing parameters"}` |
| 400 | Not an email file | `{"success": false, "error": "Not an email file"}` |
| 404 | Email not found | `{"success": false, "error": "Email not found"}` |
| 404 | Attachment not found | `{"success": false, "error": "Attachment not found"}` |
| 500 | Parse error | `{"success": false, "error": "Failed to parse nested email: ..."}` |

**Location**: `app.py:393`

---

### Serve Index

```
GET /
```

Serves the main `index.html` file.

**Location**: `app.py:455`

---

### Serve JavaScript

```
GET /js/<filename>
```

Serves JavaScript files from the `js/` directory.

**Location**: `app.py:461`

---

## Error Response Format

All errors follow this format:

```json
{
  "success": false,
  "error": "Human readable error message"
}
```

## File Limits

| Constraint | Value |
|------------|-------|
| Max file size | 10MB |
| Allowed extensions | `.eml`, `.msg` |
| Max attachments | Unlimited |

## Rate Limits

No rate limiting currently implemented.

## CORS

CORS is enabled via Flask-CORS for all origins.

## Storage Notes

- Parsed emails are stored in memory (`email_storage` dict)
- Uploaded files saved to `uploads/` directory
- Attachments extracted to `uploads/` with UUID prefix
- No automatic cleanup mechanism
