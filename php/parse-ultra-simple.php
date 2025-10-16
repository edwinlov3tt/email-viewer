<?php
/**
 * Ultra simple parser - no dependencies
 */

// Set JSON header first
header('Content-Type: application/json');

try {
    // Check request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    if (!isset($_FILES['email'])) {
        throw new Exception('No file uploaded');
    }

    $file = $_FILES['email'];
    $filename = $file['name'];

    // Read first few lines of the file to get basic headers
    $content = file_get_contents($file['tmp_name'], false, null, 0, 5000);

    // Extract basic headers with simple regex
    $from = '';
    $to = '';
    $subject = '';
    $date = '';

    if (preg_match('/From:\s*(.+?)[\r\n]/i', $content, $matches)) {
        $from = trim($matches[1]);
    }
    if (preg_match('/To:\s*(.+?)[\r\n]/i', $content, $matches)) {
        $to = trim($matches[1]);
    }
    if (preg_match('/Subject:\s*(.+?)[\r\n]/i', $content, $matches)) {
        $subject = trim($matches[1]);
    }
    if (preg_match('/Date:\s*(.+?)[\r\n]/i', $content, $matches)) {
        $date = trim($matches[1]);
    }

    // Return simple response
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => uniqid(),
            'filename' => $filename,
            'headers' => [
                'from' => $from ?: 'unknown@example.com',
                'to' => $to ?: 'unknown@example.com',
                'subject' => $subject ?: 'Email: ' . $filename,
                'date' => $date ?: date('Y-m-d H:i:s')
            ],
            'body' => [
                'html' => '<p>Email uploaded successfully. Basic parsing only (no library).</p>',
                'text' => 'Email uploaded successfully. Basic parsing only (no library).'
            ],
            'attachments' => []
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>