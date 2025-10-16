<?php
/**
 * Bulk Upload Handler
 *
 * Handles multiple email file uploads and returns parsed data for all files
 */

require_once 'config.php';
require_once 'parse-email.php';

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

// Check if files were uploaded
if (!isset($_FILES['emails']) || !is_array($_FILES['emails']['name'])) {
    echo json_encode(['success' => false, 'error' => 'No files uploaded']);
    exit;
}

// Clean old files periodically
if (rand(1, 100) <= 10) { // 10% chance to clean on bulk upload
    cleanOldFiles();
}

$results = [];
$successCount = 0;
$failureCount = 0;
$totalFiles = count($_FILES['emails']['name']);

// Process each uploaded file
for ($i = 0; $i < $totalFiles; $i++) {
    // Create single file array for validation
    $file = [
        'name' => $_FILES['emails']['name'][$i],
        'type' => $_FILES['emails']['type'][$i],
        'tmp_name' => $_FILES['emails']['tmp_name'][$i],
        'error' => $_FILES['emails']['error'][$i],
        'size' => $_FILES['emails']['size'][$i]
    ];

    // Skip if no file
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        continue;
    }

    // Validate file
    $validation = validateUploadedFile($file);
    if (!$validation['success']) {
        $results[] = [
            'success' => false,
            'filename' => $file['name'],
            'error' => $validation['error']
        ];
        $failureCount++;
        continue;
    }

    try {
        $originalName = sanitizeFilename($file['name']);
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $uploadId = generateUploadId();

        // Create temporary file path
        $tempPath = UPLOAD_DIR . $uploadId . '_' . $originalName;

        // Move uploaded file to temporary location
        if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
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

        // Add metadata
        $emailData['id'] = $uploadId;
        $emailData['filename'] = $originalName;
        $emailData['upload_time'] = date('Y-m-d H:i:s');

        // Store in session
        if (!isset($_SESSION['emails'])) {
            $_SESSION['emails'] = [];
        }

        $_SESSION['emails'][$uploadId] = [
            'path' => $tempPath,
            'data' => $emailData,
            'timestamp' => time()
        ];

        // Add to results
        $results[] = [
            'success' => true,
            'id' => $uploadId,
            'filename' => $originalName,
            'data' => $emailData
        ];
        $successCount++;

    } catch (Exception $e) {
        // Clean up temporary file if exists
        if (isset($tempPath) && file_exists($tempPath)) {
            unlink($tempPath);
        }

        $results[] = [
            'success' => false,
            'filename' => $file['name'],
            'error' => $e->getMessage()
        ];
        $failureCount++;
    }
}

// Clean up old session data
if (isset($_SESSION['emails'])) {
    foreach ($_SESSION['emails'] as $id => $email) {
        if (time() - $email['timestamp'] > SESSION_TIMEOUT) {
            if (file_exists($email['path'])) {
                unlink($email['path']);
            }
            // Clean up attachments
            if (isset($email['data']['attachments'])) {
                foreach ($email['data']['attachments'] as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path'])) {
                        unlink($attachment['path']);
                    }
                }
            }
            unset($_SESSION['emails'][$id]);
        }
    }
}

// Return response
echo json_encode([
    'success' => $successCount > 0,
    'summary' => [
        'total' => $totalFiles,
        'success' => $successCount,
        'failed' => $failureCount
    ],
    'results' => $results
]);