<?php
/**
 * Simplified Email Parser - for testing
 */

// Basic error handling
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

// Start output buffering to catch any errors
ob_start();

try {
    // Load config
    require_once __DIR__ . '/config.php';

    // Set JSON header
    header('Content-Type: application/json');

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        exit;
    }

    // Check if file was uploaded
    if (!isset($_FILES['email']) || $_FILES['email']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error']);
        exit;
    }

    $file = $_FILES['email'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // For now, just return basic file info
    $response = [
        'success' => true,
        'data' => [
            'id' => uniqid(),
            'filename' => $file['name'],
            'size' => $file['size'],
            'type' => $extension,
            'headers' => [
                'from' => 'test@example.com',
                'to' => 'recipient@example.com',
                'subject' => 'Test email - ' . $file['name'],
                'date' => date('Y-m-d H:i:s')
            ],
            'body' => [
                'html' => '<p>This is a test response. File uploaded successfully.</p>',
                'text' => 'This is a test response. File uploaded successfully.'
            ],
            'attachments' => []
        ]
    ];

    // Clear any output buffer
    ob_end_clean();

    echo json_encode($response);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
} catch (Error $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fatal: ' . $e->getMessage(),
        'line' => $e->getLine(),
        'file' => $e->getFile()
    ]);
}
?>