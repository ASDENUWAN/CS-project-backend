<?php
header("Content-Type: application/json");
require '../config.php';; // Database connection file

// Ensure the request method is PUT
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only PUT requests are allowed"]);
    exit;
}

// Get raw JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Check if required fields are provided
if (!isset($data["paymentID"], $data['paymentType'], $data['memberID'], $data['memberName'], $data['paymentDate'], $data['dueDate'], $data['amount'], $data['paymentStatus'])) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

$paymentID = ($data["paymentID"]);
$paymentType = trim($data['paymentType']);
$memberID = trim($data['memberID']);
$memberName = trim($data['memberName']);
$paymentDate = trim($data['paymentDate']);
$dueDate = trim($data['dueDate']);
$amount = trim($data['amount']);
$paymentStatus = trim($data['paymentStatus']);

try {
    // Update payment details in the database
    $stmt = $conn->prepare("UPDATE payment SET paymentType = ?, paymentDate = ?, dueDate = ?, amount = ?, paymentStatus = ? WHERE paymentID = ?");
    $stmt->bind_param("sssdsi", $paymentType, $paymentDate, $dueDate, $amount, $paymentStatus, $paymentID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Payment updated successfully"]);
    } else {
        throw new Exception("Failed to update payment");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
