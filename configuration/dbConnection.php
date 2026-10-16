<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once __DIR__ . "/NewLogger.php";

try {

    $conn = new mysqli(
        "localhost",
        "root",
        "",
        "practice"
    );

   // $conn->set_charset("utf8mb4");
   echo "Database connected";

} catch (mysqli_sql_exception $e) {

    writeLog("database.log", "Database Connection Error: " .   $e->getMessage() . " | Error Code: " .  $e->getCode(),   "DATABASE" );

    http_response_code(500);

    echo json_encode([
        "status" => false,
        "message" => "Database connection failed."
    ]);

    exit;
}