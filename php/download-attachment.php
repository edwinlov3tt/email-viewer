<?php
/**
 * Attachment Download Handler
 *
 * Handles secure download of email attachments
 */

require_once 'config.php';

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Method not allowed');
}

// Check for required parameters
if (!isset($_GET['email_id']) || !isset($_GET['attachment_id'])) {
    http_response_code(400);
    die('Missing required parameters');
}

$emailId = $_GET['email_id'];
$attachmentId = $_GET['attachment_id'];

// Validate session
if (!isset($_SESSION['emails'][$emailId])) {
    http_response_code(404);
    die('Email not found in session');
}

$email = $_SESSION['emails'][$emailId];

// Find attachment
$attachmentFound = null;
if (isset($email['data']['attachments'])) {
    foreach ($email['data']['attachments'] as $attachment) {
        if ($attachment['id'] === $attachmentId) {
            $attachmentFound = $attachment;
            break;
        }
    }
}

if (!$attachmentFound) {
    http_response_code(404);
    die('Attachment not found');
}

// Check if file exists
if (!file_exists($attachmentFound['path'])) {
    http_response_code(404);
    die('Attachment file not found');
}

// Determine content type
$contentType = isset($attachmentFound['content_type']) ? $attachmentFound['content_type'] : 'application/octet-stream';
$filename = isset($attachmentFound['filename']) ? $attachmentFound['filename'] : 'attachment';

// Security: Ensure filename doesn't contain path traversal
$filename = basename($filename);

// Set headers for download
header('Content-Type: ' . $contentType);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($attachmentFound['path']));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output file
readfile($attachmentFound['path']);
exit;