<?php

function writeLog($fileName, $message, $type = "ERROR")
{
    // Log directory
    $logDirectory = __DIR__ . "/../logs/";

    // Create directory if it doesn't exist
    if (!is_dir($logDirectory)) {
        mkdir($logDirectory, 0777, true);
    }

    // Log file
    $logFile = $logDirectory . $fileName;

    // Date and time
    $dateTime = date("Y-m-d H:i:s");

    // IP address
    $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "CLI";

    // Final log message
    $logMessage =
        "[" . $dateTime . "]" .
        " [" . $type . "]" .
        " [IP: " . $ipAddress . "] " .
        $message .
        PHP_EOL;

    // Write to file
    file_put_contents(
        $logFile,
        $logMessage,
        FILE_APPEND | LOCK_EX
    );
}