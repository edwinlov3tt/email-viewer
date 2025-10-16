<?php
/**
 * Email Viewer Configuration
 *
 * This file contains configuration settings for the email viewer application
 */

// Error reporting for development (disable in production)
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Start session for attachment management (only if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load environment variables if .env exists
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Configuration constants
define('MAX_UPLOAD_SIZE', isset($_ENV['MAX_UPLOAD_SIZE']) ? (int)$_ENV['MAX_UPLOAD_SIZE'] : 10485760); // 10MB default
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('ALLOWED_EXTENSIONS', ['eml', 'msg']);
define('SESSION_TIMEOUT', isset($_ENV['SESSION_TIMEOUT']) ? (int)$_ENV['SESSION_TIMEOUT'] : 3600); // 1 hour
define('TEMP_FILE_RETENTION', isset($_ENV['TEMP_FILE_RETENTION']) ? (int)$_ENV['TEMP_FILE_RETENTION'] : 3600); // 1 hour

// Database configuration (optional)
define('DB_ENABLED', isset($_ENV['DB_HOST']) && $_ENV['DB_HOST']);
if (DB_ENABLED) {
    define('DB_HOST', $_ENV['DB_HOST']);
    define('DB_NAME', $_ENV['DB_NAME']);
    define('DB_USER', $_ENV['DB_USER']);
    define('DB_PASS', $_ENV['DB_PASS']);
}

// Only set headers if they haven't been sent yet
if (!headers_sent()) {
    // Security headers
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // CORS settings (adjust for your domain)
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}

// Autoload Composer dependencies
$autoloadFile = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require_once $autoloadFile;
} else {
    // Send JSON error if autoloader is missing
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Vendor autoload not found. Run: composer install --no-dev --optimize-autoloader'
    ]));
}

/**
 * Sanitize filename to prevent directory traversal
 */
function sanitizeFilename($filename) {
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    return $filename;
}

/**
 * Generate unique ID for uploads
 */
function generateUploadId() {
    return uniqid('email_', true);
}

/**
 * Format file size for display
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

/**
 * Clean old temporary files
 */
function cleanOldFiles() {
    $files = glob(UPLOAD_DIR . '*');
    $now = time();

    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) > TEMP_FILE_RETENTION) {
                unlink($file);
            }
        }
    }
}

/**
 * Validate uploaded file
 */
function validateUploadedFile($file) {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed with error code: ' . $file['error']];
    }

    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed size of ' . formatFileSize(MAX_UPLOAD_SIZE)];
    }

    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file type. Only .eml and .msg files are allowed'];
    }

    // Additional MIME type check
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = [
        'message/rfc822',
        'application/vnd.ms-outlook',
        'application/octet-stream', // Some .msg files report as this
        'text/plain', // Some .eml files report as this
    ];

    if (!in_array($mimeType, $allowedMimes)) {
        return ['success' => false, 'error' => 'Invalid file MIME type'];
    }

    return ['success' => true];
}

/**
 * Database connection helper (optional)
 */
function getDbConnection() {
    if (!DB_ENABLED) {
        return null;
    }

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}