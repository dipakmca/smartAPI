<?php

$url = "http://localhost/allApi/studentRelated/api/studentRegistration.php";

$token = "bc6b7e54763f3bfe1afffebb96aea1ba8f6bc4b3fcaf8c1fabf42d2bbd674db7";

$data = [
    "userId" => 1003,
    "studentName" => "Rahul Kumar",
     "emailId" => "santa@gmail.com",
     "password" => "suravei",
     "mobileNo" => "9692833923",
];

$jsonData = json_encode($data);

$options = [
    "http" => [
        "method"  => "POST",
        "header"  => 
            "Authorization: Bearer " . $token . "\r\n" .
            "Content-Type: application/json\r\n" .
            "Content-Length: " . strlen($jsonData) . "\r\n",
        "content" => $jsonData,
        "ignore_errors" => true,
        "timeout" => 30
    ]
];

$context = stream_context_create($options);

$response = file_get_contents($url, false, $context);

if ($response === false) {

    echo json_encode([
        "status" => false,
        "message" => "Unable to connect to API"
    ]);

    exit;
}

// API response
echo $response;