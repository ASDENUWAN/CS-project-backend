<?php
header("Content-Type: application/json");
require '../config.php';; // Database connection file

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

// Get raw JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input fields
if (!isset($data['paymentType'], $data['memberID'], $data['memberName'], $data['paymentDate'], $data['dueDate'], $data['amount'], $data['paymentStatus'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide all required fields."]);
    exit;
}

$paymentType = trim($data['paymentType']);
$memberID = trim($data['memberID']);
$memberName = trim($data['memberName']);
$paymentDate = trim($data['paymentDate']);
$dueDate = trim($data['dueDate']);
$amount = trim($data['amount']);
$paymentStatus = trim($data['paymentStatus']);

// Prepare SQL statement to insert employee data
$stmt = $conn->prepare("INSERT INTO payment (paymentType, memberID, memberName, paymentDate, dueDate, amount, paymentStatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisssis", $paymentType, $memberID, $memberName, $paymentDate, $dueDate, $amount, $paymentStatus);

if ($stmt->execute()) {
    ob_clean(); // Clear any unwanted output
    echo json_encode(["success" => true, "message" => "Payment added successfully!"]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to add payment"]);
}

$stmt->close();
$conn->close();
exit;
