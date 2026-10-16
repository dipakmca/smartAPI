<?php

function verifyBearerToken($conn)
{
    /*
     * Get Authorization header
     */
    $headers = getallheaders();

    $authorization = $headers["Authorization"]
        ?? $headers["authorization"]
        ?? "";

    /*
     * Authorization header missing
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
     */
    if (!preg_match(
        '/^Bearer\s+(.+)$/i',
        trim($authorization),
        $matches
    )) {

        http_response_code(401);

        echo json_encode([
            "status" => false,
            "message" => "Invalid Bearer token."
        ]);

        exit;
    }

    /*
     * Get token
     */
    $token = trim($matches[1]);

    /*
     * Hash token
     */
    $tokenHash =  $token ;

    /*
     * Check token and expiry
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

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "s",
        $tokenHash
    );

    $stmt->execute();

    $result = $stmt->get_result();

    /*
     * Token invalid or expired
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
     * Token verified
     */
    $tokenData = $result->fetch_assoc();

    /*
     * Return user information
     */
    return [
        "tokenId" => $tokenData["id"],
        "userId" => $tokenData["user_id"],
        "expiresAt" => $tokenData["expires_at"]
    ];
}