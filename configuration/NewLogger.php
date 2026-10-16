<?php

function writeLog($module, $message, $type = "ERROR")
{
    $logDirectory = __DIR__ . "/../Logs/";

    if (!is_dir($logDirectory)) {
        mkdir($logDirectory, 0777, true);
    }

    // Example:
    // database_2026-10-06.log
    // login_2026-10-06.log

    $date = date("Y-m-d");

    $logFile = $logDirectory .
               $module . "_" .
               $date . ".log";

    $dateTime = date("Y-m-d H:i:s");

    $ipAddress = $_SERVER["REMOTE_ADDR"] ?? "CLI";

    $logMessage =
        "[" . $dateTime . "] " .
        "[" . $type . "] " .
        "[IP: " . $ipAddress . "] " .
        $message .
        PHP_EOL;

    file_put_contents(
        $logFile,
        $logMessage,
        FILE_APPEND | LOCK_EX
    );
}