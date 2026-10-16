<?php

function getBearerToken()
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    // Apache/WAMP may expose Authorization through $_SERVER
    if (empty($headers['Authorization']) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if (isset($headers['Authorization'])) {
        $authorization = trim($headers['Authorization']);

        if (preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            return trim($matches[1]);
        }
    }

    return null;
}

function verifyToken($conn, $userId, $token)
{
    if ($userId <= 0 || empty($token)) {
        return false;
    }

    // Expected token table:
    // user_tokens(userId, token, expiresAt)
    $sql = "SELECT user_id
            FROM api_tokens
            WHERE user_id = ?
              AND token_hash = ?
              AND expires_at > NOW()
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows === 1;
}
