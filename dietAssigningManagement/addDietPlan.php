<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

// Get raw JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Check for decoding errors
if ($data === null) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

// Validate input fields
if (!isset($data['memberID'], $data['dietID'], $data['startDate'], $data['endDate'], $data['dpStatus'], $data['height'], $data['weightKG'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide all required fields."]);
    exit;
}

$memberID = intval($data['memberID']);
$dietID = intval($data['dietID']);
$startDate = trim($data['startDate']);
$endDate = trim($data['endDate']);
$dpStatus = trim($data['dpStatus']);
$height = floatval($data['height']);
$weightKG = floatval($data['weightKG']);

// Prepare SQL statement to insert diet plan data
$stmt = $conn->prepare("INSERT INTO dietplan (memberID, dietID, startDate, endDate, dpStatus, height, weightKG) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssdd", $memberID, $dietID, $startDate, $endDate, $dpStatus, $height, $weightKG);

if ($stmt->execute()) {
    ob_clean(); // Clear any unwanted output
    echo json_encode(["success" => true, "message" => "Diet Plan added successfully!"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to add diet plan"]);
}

$stmt->close();
$conn->close();
exit;
