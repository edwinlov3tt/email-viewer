<?php
// Display recent PHP errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>PHP Error Check</h2>";
echo "<pre>";

// Check various error logs
$possibleLogs = [
    __DIR__ . '/../error.log',
    __DIR__ . '/../logs/error.log',
    '/home/customer/logs/ignite.edwinlovett.com/http_error.log',
    ini_get('error_log')
];

foreach ($possibleLogs as $log) {
    if ($log && file_exists($log)) {
        echo "Found log at: $log\n";
        echo "Last 20 lines:\n";
        echo "-------------------\n";
        $lines = file($log);
        if ($lines) {
            $recent = array_slice($lines, -20);
            foreach ($recent as $line) {
                echo htmlspecialchars($line);
            }
        }
        echo "\n-------------------\n\n";
    }
}

// Also check PHP info
echo "\nPHP Version: " . PHP_VERSION . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "</pre>";
?>