<?php

header("Content-Type: application/json; charset=utf-8");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once "../configuration/dbConnection.php";
require_once "auth.php";

try {

    // Get JSON request
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid JSON request"
        ]);
        exit;
    }

    // Get input values
    $userId      = isset($input["userId"]) ? (int)$input["userId"] : 0;
    $studentName = isset($input["studentName"]) ? trim((string)$input["studentName"]) : '';
    $emailId     = isset($input["emailId"]) ? trim((string)$input["emailId"]) : '';
    $password    = isset($input["password"]) ? (string)$input["password"] : '';
    $mobileNo    = isset($input["mobileNo"]) ? trim((string)$input["mobileNo"]) : '';

    // Validate input
    if ($userId <= 0) {
        echo json_encode(["status" => false, "message" => "Invalid userId"]);
        exit;
    } elseif ($studentName === '') {
        echo json_encode(["status" => false, "message" => "Student name is required"]);
        exit;
    } elseif ($emailId === '' || !filter_var($emailId, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => false, "message" => "Valid email ID is required"]);
        exit;
    } elseif ($password === '') {
        echo json_encode(["status" => false, "message" => "Password is required"]);
        exit;
    } elseif (strlen($password) < 6) {
        echo json_encode(["status" => false, "message" => "Password must be at least 6 characters"]);
        exit;
    } elseif (!preg_match('/^[0-9]{10}$/', $mobileNo)) {
        echo json_encode(["status" => false, "message" => "Mobile number must be 10 digits"]);
        exit;
    }

    // Get Bearer Token
    $token = getBearerToken();

    // Verify token and expiry
    if (!verifyToken($conn, $userId, $token)) {
        echo json_encode([
            "status" => false,
            "message" => "Invalid or expired bearer token"
        ]);
        exit;
    }

    // Hash password before storing it
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert student
    $sql = "INSERT INTO student_registration
            (studentName, emailId, password, mobileNo, status)
            VALUES (?, ?, ?, ?, 1)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssss",
        $studentName,
        $emailId,
        $hashedPassword,
        $mobileNo
    );

    $stmt->execute();

    echo json_encode([
        "status" => true,
        "message" => "Data inserted successfully",
        "studentId" => $stmt->insert_id
    ]);

} catch (mysqli_sql_exception $e) {
writeLog(
        "database",
        "Token API Error: " .
        $e->getMessage() .
        " | Error Code: " .
        $e->getCode(),
        "DATABASE"
    );
    echo json_encode([
        "status" => false,
        "message" => "Database error"
    ]);

} catch (Exception $e) {
writeLog(
        "database",
        "Token API Error: " .
        $e->getMessage() .
        " | Error Code: " .
        $e->getCode(),
        "DATABASE"
    );
    echo json_encode([
        "status" => false,
        "message" => "Server error"
    ]);
}
