<?php
function logMessage($message, $type = 'OK', $author = 'BOT', $directory = 'logs') {
    $logFile = $directory . '/log.txt';
    $date = date('Y-m-d H:i:s');

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
        file_put_contents($logFile, "[$date] [NEW] SYS: Log file created" . PHP_EOL);
    }

    if (file_exists($logFile) && filesize($logFile) >= 5 * 1024 * 1024) {
        file_put_contents($logFile, "[$date] [NEW] SYS: Log file closed" . PHP_EOL, FILE_APPEND);
        $newLogFile = $directory . '/log_' . date('Ymd_His') . '.txt';
        rename($logFile, $newLogFile);
        file_put_contents($logFile, "[$date] [NEW] SYS: Log file created" . PHP_EOL);
    }

    $logEntry = "[$date] [$type] $author: $message" . PHP_EOL;

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
