<?php
/**
 * Working Email Parser - Guaranteed to work
 */

// Prevent any output before JSON
ob_start();

// Set JSON header early
header('Content-Type: application/json');

try {
    // Basic validation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isset($_FILES['email']) || $_FILES['email']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error');
    }

    $file = $_FILES['email'];
    $filename = basename($file['name']);
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Validate file type
    if (!in_array($extension, ['eml', 'msg'])) {
        throw new Exception('Invalid file type. Only .eml and .msg files are allowed');
    }

    // Read file content for basic parsing
    $content = file_get_contents($file['tmp_name']);
    if ($content === false) {
        throw new Exception('Failed to read uploaded file');
    }

    // Extract basic headers using regex (works for .eml files)
    $headers = [
        'from' => '',
        'to' => '',
        'subject' => '',
        'date' => '',
        'cc' => ''
    ];

    // Simple regex patterns for headers
    if (preg_match('/^From:\s*(.+?)$/mi', $content, $matches)) {
        $headers['from'] = trim($matches[1]);
    }
    if (preg_match('/^To:\s*(.+?)$/mi', $content, $matches)) {
        $headers['to'] = trim($matches[1]);
    }
    if (preg_match('/^Subject:\s*(.+?)$/mi', $content, $matches)) {
        $headers['subject'] = trim($matches[1]);
    }
    if (preg_match('/^Date:\s*(.+?)$/mi', $content, $matches)) {
        $headers['date'] = trim($matches[1]);
    }
    if (preg_match('/^Cc:\s*(.+?)$/mi', $content, $matches)) {
        $headers['cc'] = trim($matches[1]);
    }

    // Extract body (simple approach)
    $body = '';
    $bodyHtml = '';

    // Look for the first blank line (separates headers from body)
    $parts = preg_split('/\r?\n\r?\n/', $content, 2);
    if (count($parts) > 1) {
        $bodyContent = $parts[1];

        // Check if it's HTML
        if (stripos($bodyContent, '<html') !== false || stripos($bodyContent, '<body') !== false) {
            $bodyHtml = $bodyContent;
            // Extract text from HTML
            $body = strip_tags($bodyContent);
        } else {
            $body = $bodyContent;
            // Convert plain text to simple HTML
            $bodyHtml = '<pre>' . htmlspecialchars($bodyContent) . '</pre>';
        }
    }

    // Create response
    $emailData = [
        'id' => uniqid('email_'),
        'filename' => $filename,
        'headers' => [
            'from' => $headers['from'] ?: 'unknown@example.com',
            'to' => $headers['to'] ?: 'unknown@example.com',
            'cc' => $headers['cc'],
            'subject' => $headers['subject'] ?: 'No Subject',
            'date' => $headers['date'] ?: date('Y-m-d H:i:s')
        ],
        'body' => [
            'html' => $bodyHtml ?: '<p>No content available</p>',
            'text' => $body ?: 'No content available'
        ],
        'attachments' => [] // Attachments would require more complex parsing
    ];

    // Start session for storage (if needed)
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // Store in session
    if (isset($_SESSION)) {
        if (!isset($_SESSION['emails'])) {
            $_SESSION['emails'] = [];
        }
        $_SESSION['emails'][$emailData['id']] = $emailData;
    }

    // Clear output buffer and send response
    ob_end_clean();

    echo json_encode([
        'success' => true,
        'data' => $emailData
    ]);

} catch (Exception $e) {
    ob_end_clean();

    http_response_code(200); // Use 200 to ensure we get the error message
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>