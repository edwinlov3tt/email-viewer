<?php
// Minimal parse test with error capture
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Test if we can load config
    if (!file_exists('config.php')) {
        die("config.php not found");
    }

    require_once 'config.php';
    echo "Config loaded successfully\n";

    // Test if classes are available
    if (class_exists('ZBateson\MailMimeParser\MailMimeParser')) {
        echo "MailMimeParser class found\n";
    } else {
        echo "MailMimeParser class NOT found\n";
    }

    // Try to create parser
    $parser = new ZBateson\MailMimeParser\MailMimeParser();
    echo "Parser created successfully\n";

    // If we get here, basic parsing should work
    echo json_encode(['success' => true, 'message' => 'All checks passed']);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>