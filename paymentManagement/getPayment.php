<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file
ob_clean();
// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

// Get JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input
if (!isset($data["paymentID"])) {
    echo json_encode(["success" => false, "message" => "Payment ID is required"]);
    exit;
}

$paymentID = $data["paymentID"];

// Fetch employee details from database
$stmt = $conn->prepare("SELECT paymentID, paymentType, memberID, memberName, paymentDate, dueDate, amount, paymentStatus FROM payment WHERE paymentID = ?");
$stmt->bind_param("i", $paymentID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $payment = $result->fetch_assoc();
    echo json_encode(["success" => true, "payment" => $payment]);
} else {
    echo json_encode(["success" => false, "message" => "Payment not found"]);
}

$stmt->close();
$conn->close();