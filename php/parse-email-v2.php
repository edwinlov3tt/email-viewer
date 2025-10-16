<?php
/**
 * Email Parser V2 - With better error handling
 */

ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

// Load config and autoloader FIRST
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Use statements must be at the top level
use ZBateson\MailMimeParser\MailMimeParser;

// Start output buffering to catch any errors
ob_start();

try {

    // Set JSON header
    header('Content-Type: application/json');

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    // Check if file was uploaded
    if (!isset($_FILES['email']) || $_FILES['email']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['email'];

    // Validate file
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['eml', 'msg'])) {
        throw new Exception('Invalid file type. Only .eml and .msg files are allowed');
    }

    if ($file['size'] > 10485760) { // 10MB
        throw new Exception('File size exceeds 10MB limit');
    }

    // Generate unique ID and move file
    $uploadId = uniqid('email_', true);
    $filename = basename($file['name']);
    $tempPath = UPLOAD_DIR . $uploadId . '_' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    try {
        // Parse based on file type
        if ($extension === 'eml') {
            $emailData = parseEmlFileSimple($tempPath);
        } else {
            // For now, return basic data for MSG files
            $emailData = [
                'headers' => [
                    'from' => 'msg-sender@example.com',
                    'to' => 'recipient@example.com',
                    'subject' => 'MSG File: ' . $filename,
                    'date' => date('Y-m-d H:i:s')
                ],
                'body' => [
                    'html' => '<p>MSG file parsing will be implemented soon.</p>',
                    'text' => 'MSG file parsing will be implemented soon.'
                ],
                'attachments' => []
            ];
        }

        // Add metadata
        $emailData['id'] = $uploadId;
        $emailData['filename'] = $filename;

        // Store in session
        if (!isset($_SESSION['emails'])) {
            $_SESSION['emails'] = [];
        }
        $_SESSION['emails'][$uploadId] = [
            'path' => $tempPath,
            'data' => $emailData,
            'timestamp' => time()
        ];

        // Clear output buffer
        ob_end_clean();

        // Return success
        echo json_encode([
            'success' => true,
            'data' => $emailData
        ]);

    } catch (Exception $e) {
        // Clean up file if parsing failed
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        throw $e;
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Simple EML parser function
 */
function parseEmlFileSimple($filePath) {
    try {
        $parser = new MailMimeParser();

        // Read file content
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception('Failed to read email file');
        }

        // Parse the message
        $message = $parser->parse($content);

        // Extract basic headers
        $headers = [
            'from' => $message->getHeaderValue('from') ?: '',
            'to' => $message->getHeaderValue('to') ?: '',
            'cc' => $message->getHeaderValue('cc') ?: '',
            'subject' => $message->getHeaderValue('subject') ?: 'No Subject',
            'date' => $message->getHeaderValue('date') ?: date('Y-m-d H:i:s')
        ];

        // Extract body
        $bodyHtml = $message->getHtmlContent();
        $bodyText = $message->getTextContent();

        // If we have HTML, sanitize it
        if ($bodyHtml && class_exists('HTMLPurifier')) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
            $purifier = new HTMLPurifier($config);
            $bodyHtml = $purifier->purify($bodyHtml);
        }

        // Handle attachments (simplified for now)
        $attachments = [];
        try {
            $attachmentParts = $message->getAllAttachmentParts();
            foreach ($attachmentParts as $attachment) {
                $attachmentId = uniqid('att_');
                $filename = $attachment->getFilename() ?: 'attachment';

                $attachments[] = [
                    'id' => $attachmentId,
                    'filename' => $filename,
                    'size' => strlen($attachment->getContent()),
                    'content_type' => $attachment->getContentType()
                ];
            }
        } catch (Exception $e) {
            // Ignore attachment errors for now
        }

        return [
            'headers' => $headers,
            'body' => [
                'html' => $bodyHtml ?: '<p>' . htmlspecialchars($bodyText ?: 'No content') . '</p>',
                'text' => $bodyText ?: ''
            ],
            'attachments' => $attachments
        ];

    } catch (Exception $e) {
        // If parsing fails, return basic structure
        return [
            'headers' => [
                'from' => 'unknown@example.com',
                'to' => 'unknown@example.com',
                'subject' => 'Parse Error: ' . basename($filePath),
                'date' => date('Y-m-d H:i:s')
            ],
            'body' => [
                'html' => '<p>Error parsing email: ' . htmlspecialchars($e->getMessage()) . '</p>',
                'text' => 'Error parsing email: ' . $e->getMessage()
            ],
            'attachments' => []
        ];
    }
}
?>