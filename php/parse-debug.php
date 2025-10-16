<?php
/**
 * Debug version to find the exact error
 */

// Capture all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Start with JSON header
header('Content-Type: application/json');

// Simple test first
echo json_encode(['status' => 'Starting...']);
flush();

// Test 1: Can we include config?
if (!file_exists(__DIR__ . '/config.php')) {
    die(json_encode(['error' => 'config.php not found']));
}

// Don't actually include it yet, just test
echo json_encode(['status' => 'Config file exists']);
flush();

// Test 2: Can we check vendor?
$vendorPath = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($vendorPath)) {
    die(json_encode(['error' => 'Vendor autoload not found']));
}

echo json_encode(['status' => 'Vendor exists']);
flush();

// Test 3: Try to include config
try {
    // Suppress any output from config
    ob_start();
    require_once __DIR__ . '/config.php';
    ob_end_clean();

    echo json_encode(['status' => 'Config loaded']);
    flush();
} catch (Exception $e) {
    ob_end_clean();
    die(json_encode(['error' => 'Config error: ' . $e->getMessage()]));
}

// Test 4: Try to load parser
try {
    if (!class_exists('ZBateson\MailMimeParser\MailMimeParser')) {
        die(json_encode(['error' => 'MailMimeParser class not found after loading vendor']));
    }

    $parser = new ZBateson\MailMimeParser\MailMimeParser();
    echo json_encode(['status' => 'Parser created successfully']);
    flush();
} catch (Exception $e) {
    die(json_encode(['error' => 'Parser error: ' . $e->getMessage()]));
}

// If we get here, everything should work
echo json_encode(['success' => true, 'message' => 'All systems operational']);
?>