<?php

header("Content-Type: application/json; charset=utf-8");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once "../configuration/dbConnection.php";
require_once "../configuration/NewLogger.php";

try {

    /*
     * ==========================================
     * STEP 1: Get Authorization Header
     * ==========================================
     */

    $headers = getallheaders();

    $authorization = $headers["Authorization"]
        ?? $headers["authorization"]
        ?? "";


    /*
     * ==========================================
     * STEP 2: Check Bearer Token
     * ==========================================
     */

    if (empty($authorization)) {

        http_response_code(401);

        echo json_encode([
            "status" => false,
            "message" => "Authorization header is required."
        ]);

        exit;
    }


    /*
     * Check Bearer format
     *
     * Authorization:
     * Bearer xxxxxxxxxxxxx
     */

    if (!preg_match(
        '/^Bearer\s+(.+)$/i',
        trim($authorization),
        $matches
    )) {

        http_response_code(401);

        echo json_encode([
            "status" => false,
            "message" => "Invalid Bearer token format."
        ]);

        exit;
    }


    /*
     * Get actual token
     */

    $token = trim($matches[1]);


    /*
     * ==========================================
     * STEP 3: Hash Token
     * ==========================================
     */

    $tokenHash = $token ;


    /*
     * ==========================================
     * STEP 4: Check Token
     * ==========================================
     *
     * Token must:
     *
     * 1. Exist
     * 2. Not be expired
     */

    $sql = "
        SELECT
            id,
            user_id,
            expires_at
        FROM api_tokens
        WHERE token_hash = ?
        AND expires_at > NOW()
        LIMIT 1
    ";

//echo  $sql = " SELECT  id, user_id, expires_at   FROM api_tokens WHERE token_hash ='" . $tokenHash."' AND expires_at > NOW()  LIMIT 1   ";


    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "s",
        $tokenHash
    );

    $stmt->execute();

    $result = $stmt->get_result();

//echo $result->num_rows. "ffffff";
    /*
     * ==========================================
     * STEP 5: Token Invalid
     * ==========================================
     */

    if ($result->num_rows == 0) {

        http_response_code(401);

        echo json_encode([
            "status" => false,
            "message" => "Invalid or expired token."
        ]);

        exit;
    }


    /*
     * ==========================================
     * STEP 6: Token Valid
     * ==========================================
     */

    $tokenData = $result->fetch_assoc();

    echo $tokenId = $tokenData["id"];

   echo $userId = $tokenData["user_id"];

 echo   $expiresAt = $tokenData["expires_at"];


    /*
     * ==========================================
     * STEP 7: TOKEN IS VALID
     * ==========================================
     *
     * Execute your actual query here.
     */


    /*
     * Example query
     */

    $sql = "
        SELECT *
        FROM api_tokens
        WHERE user_id = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $userId
    );

    $stmt->execute();

    $result = $stmt->get_result();


    /*
     * Convert result to array
     */

    $data = [];

    while ($row = $result->fetch_assoc()) {

        $data[] = $row;
    }


    /*
     * ==========================================
     * SUCCESS
     * ==========================================
     */

    http_response_code(200);

    echo json_encode([
        "status" => true,
        "message" => "Token verified successfully.",
        "data" => $data
    ]);


} catch (mysqli_sql_exception $e) {

    /*
     * Log database error
     */

    writeLog(
        "database",
        "Bearer Token API Error: " .
        $e->getMessage() .
        " | Error Code: " .
        $e->getCode(),
        "DATABASE"
    );


    /*
     * Error response
     */

    http_response_code(500);

    echo json_encode([
        "status" => false,
        "message" => "Internal server error."
    ]);
}

