<?php
/**
 * Cleanup Script
 *
 * This script should be run periodically via cron to clean up old temporary files
 * Cron example: 0 * * * * /usr/bin/php /path/to/cleanup.php
 */

require_once 'config.php';

// Log function
function logCleanup($message) {
    $logFile = dirname(__DIR__) . '/logs/cleanup.log';
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Start cleanup
logCleanup("Starting cleanup process");

$deletedFiles = 0;
$totalSize = 0;

try {
    // Clean uploads directory
    $files = glob(UPLOAD_DIR . '*');
    $now = time();

    foreach ($files as $file) {
        if (is_file($file)) {
            $age = $now - filemtime($file);

            // Delete files older than retention period
            if ($age > TEMP_FILE_RETENTION) {
                $size = filesize($file);
                if (unlink($file)) {
                    $deletedFiles++;
                    $totalSize += $size;
                    logCleanup("Deleted: " . basename($file) . " (age: " . round($age / 3600, 1) . " hours, size: " . formatFileSize($size) . ")");
                } else {
                    logCleanup("Failed to delete: " . basename($file));
                }
            }
        }
    }

    // Clean up database records if database is enabled
    if (DB_ENABLED) {
        $db = getDbConnection();
        if ($db) {
            // Clean old upload records
            $stmt = $db->prepare("DELETE FROM email_uploads WHERE created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)");
            $stmt->execute([TEMP_FILE_RETENTION]);
            $dbDeleted = $stmt->rowCount();

            if ($dbDeleted > 0) {
                logCleanup("Deleted $dbDeleted old database records");
            }
        }
    }

    // Log summary
    if ($deletedFiles > 0) {
        logCleanup("Cleanup completed: Deleted $deletedFiles files, freed " . formatFileSize($totalSize));
    } else {
        logCleanup("Cleanup completed: No files to delete");
    }

} catch (Exception $e) {
    logCleanup("Error during cleanup: " . $e->getMessage());
    exit(1);
}

// Success
exit(0);