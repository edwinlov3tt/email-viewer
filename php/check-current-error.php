<?php
// Check the most recent errors
header('Content-Type: text/plain');

echo "Checking for recent errors...\n\n";

// Clear any existing output
if (ob_get_level()) ob_end_clean();

// Try to trigger and catch any errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check error log
$errorLog = __DIR__ . '/../error.log';
if (file_exists($errorLog)) {
    echo "Recent errors from error.log:\n";
    $lines = file($errorLog);
    $recent = array_slice($lines, -10);
    foreach ($recent as $line) {
        echo $line;
    }
    echo "\n";
}

// Test if we can create the parser
echo "Testing parser creation:\n";
try {
    require_once __DIR__ . '/config.php';
    require_once dirname(__DIR__) . '/vendor/autoload.php';

    echo "- Config loaded\n";
    echo "- Autoloader loaded\n";

    use ZBateson\MailMimeParser\MailMimeParser;

    $parser = new MailMimeParser();
    echo "- Parser created successfully\n";

    // Test parsing a simple email string
    $testEmail = "From: test@example.com\r\nTo: recipient@example.com\r\nSubject: Test\r\n\r\nTest body";
    $message = $parser->parse($testEmail);
    echo "- Test email parsed\n";
    echo "  From: " . $message->getHeaderValue('from') . "\n";
    echo "  Subject: " . $message->getHeaderValue('subject') . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

// Check PHP error log
$phpErrorLog = ini_get('error_log');
if ($phpErrorLog && file_exists($phpErrorLog)) {
    echo "\nRecent PHP errors:\n";
    $lines = file($phpErrorLog);
    $recent = array_slice($lines, -5);
    foreach ($recent as $line) {
        if (strpos($line, 'email-viewer') !== false) {
            echo $line;
        }
    }
}
?>