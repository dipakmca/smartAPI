
<?php
header("Content-Type: application/json; charset=utf-8");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once "../configuration/dbConnection.php";
require_once "../configuration/NewLogger.php";

try {

    /*
     * Get JSON request
     */
    $input = json_decode(
        file_get_contents("php://input"),
        true
    );

    /*
     * Validate JSON input
     */
    if (!is_array($input)) {

        http_response_code(400);

        echo json_encode([
            "status" => false,
            "message" => "Invalid JSON request."
        ]);

    }

    /*
     * ELSE IF
     * JSON is valid
     */
    elseif (is_array($input)) {

        /*
         * Get User ID
         */
        $userId = (int)($input["userId"] ?? 0);

        /*
         * IF
         * Validate User ID
         */
        if ($userId <= 0) {

            http_response_code(400);

            echo json_encode([
                "status" => false,
                "message" => "Invalid user ID."
            ]);

        }

        /*
         * ELSE IF
         * User ID is valid
         */
        elseif ($userId > 0) {

            /*
             * Check user
             */
            $sql = "
                SELECT
                    id,
                    user_id
                FROM api_tokens
                WHERE user_id = ?
                LIMIT 1
            ";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "i",
                $userId
            );

            $stmt->execute();

            $result = $stmt->get_result();

            /*
             * IF
             * User not found
             */
            if ($result->num_rows > 0) 
			{
				
				http_response_code(401);
				echo json_encode([ "status" => false, "message" => "Token already generated and activated for this userid " . $userId]);
			}
			
			
			else{

              

                /*
                 * Generate secure token
                 */
                $token = bin2hex(
                    random_bytes(64)
                );

                /*
                 * Generate token hash
                 */
                $tokenHash = hash(
                    "sha256",
                    $token
                );

                /*
                 * Token valid for 1 hour
                 */
                $expiresAt = date(
                    "Y-m-d H:i:s",
                    strtotime("+1 year")
                );

                /*
                 * Insert new token
                 */
                $sql = "
                    INSERT INTO api_tokens
                    (
                        user_id,
                        token_hash,
                        expires_at
                    )
                    VALUES (?, ?, ?)
                ";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "iss",
                    $userId,
                    $tokenHash,
                    $expiresAt
                );

                $stmt->execute();

                /*
                 * Get Token ID
                 */
                $tokenId = $conn->insert_id;

                /*
                 * Write log
                 */
                writeLog(
                    "authentication",
                    "Token created successfully. " .
                    "User ID: " . $userId .
                    " | Token ID: " . $tokenId,
                    "TOKEN"
                );

                /*
                 * Success response
                 */
                http_response_code(200);

                echo json_encode([
                    "status" => true,
                    "message" => "Token created successfully.",
                    "data" => [
                        "id" => $tokenId,
                        "userId" => $userId,
                        "token" => $token,
                        "expiresAt" => $expiresAt
                    ]
                ]);
            }
			
			
				
			
        }
    }

} catch (mysqli_sql_exception $e) {

    /*
     * Log database error
     */
    writeLog(
        "database",
        "Token API Error: " .
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
        "message" => "Unable to create token."
    ]);
}

