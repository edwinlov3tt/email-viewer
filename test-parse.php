<?php
// Test file to check PHP configuration and libraries

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "PHP Version: " . PHP_VERSION . "\n\n";

// Check if vendor/autoload exists
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    echo "✓ Vendor autoload file exists\n";
    require_once $autoloadFile;

    // Test if classes are available
    if (class_exists('ZBateson\MailMimeParser\MailMimeParser')) {
        echo "✓ ZBateson MailMimeParser is loaded\n";
    } else {
        echo "✗ ZBateson MailMimeParser NOT loaded\n";
    }

    if (class_exists('HTMLPurifier')) {
        echo "✓ HTMLPurifier is loaded\n";
    } else {
        echo "✗ HTMLPurifier NOT loaded\n";
    }
} else {
    echo "✗ Vendor autoload file NOT found at: $autoloadFile\n";
    echo "  Run: composer install --no-dev --optimize-autoloader\n";
}

// Check uploads directory
$uploadsDir = __DIR__ . '/uploads';
if (is_dir($uploadsDir)) {
    echo "✓ Uploads directory exists\n";
    if (is_writable($uploadsDir)) {
        echo "✓ Uploads directory is writable\n";
    } else {
        echo "✗ Uploads directory is NOT writable - run: chmod 777 uploads\n";
    }
} else {
    echo "✗ Uploads directory does NOT exist - run: mkdir uploads && chmod 777 uploads\n";
}

// Check PHP extensions
echo "\nPHP Extensions:\n";
echo "- mbstring: " . (extension_loaded('mbstring') ? '✓ Loaded' : '✗ Not loaded') . "\n";
echo "- iconv: " . (extension_loaded('iconv') ? '✓ Loaded' : '✗ Not loaded') . "\n";
echo "- fileinfo: " . (extension_loaded('fileinfo') ? '✓ Loaded' : '✗ Not loaded') . "\n";

// Check session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "\n✓ Session can be started\n";
} else {
    echo "\n✓ Session already active\n";
}

echo "\nIf all checks pass, the email parser should work!\n";
?>