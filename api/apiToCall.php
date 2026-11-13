
<?php

// 1. CORS configuration
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// 2. Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => false,
        "message" => "Only POST method is allowed"
    ]);
    exit();
}

// 4. Read JSON data from Angular
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "Invalid JSON data"
    ]);
    exit();
}

// 5. Get parameters
$studentName = trim($input['studentName'] ?? '');
$emailId     = trim($input['email'] ?? '');
$password    = $input['password'] ?? '';
$mobileNo    = trim($input['mobile'] ?? '');

// 6. Validate fields
if (
    $studentName === '' ||
    $emailId === '' ||
    $password === '' ||
    $mobileNo === ''
) {
    http_response_code(400);
    echo json_encode([
        "status" => false,
        "message" => "All fields are required"
    ]);
    exit();
}

// 7. API configuration
$url = "http://localhost/smartAPI/api/studentRegistration.php";

$token = "157872d4565d59a10fe379180d98e88721317157843ed20fa8eb726e0bdca541";
$userid = 1012;

// 8. Prepare JSON data
$data = [
    "userId" => $userid,
    "studentName" => $studentName,
    "emailId" => $emailId,
    "password" => $password,
    "mobileNo" => $mobileNo
];

$jsonData = json_encode($data);

// 9. Configure API request
$options = [
    "http" => [
        "method" => "POST",
        "header" =>
            "Authorization: Bearer " . $token . "\r\n" .
            "Content-Type: application/json\r\n" .
            "Accept: application/json\r\n",
        "content" => $jsonData,
        "ignore_errors" => true,
        "timeout" => 30
    ]
];

$context = stream_context_create($options);

// 10. Call registration API
$response = file_get_contents($url, false, $context);

// 11. Handle connection error
if ($response === false) {
    http_response_code(502);

    echo json_encode([
        "status" => false,
        "message" => "Unable to connect to student registration API"
    ]);

    exit();
}

// 12. Return API response
echo $response;

?>
