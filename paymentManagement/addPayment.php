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
if (!isset($data['paymentType'], $data['memberID'], $data['memberName'], $data['paymentDate'], $data['dueDate'], $data['amount'])) {
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
$paymentStatus = 'Paid';

try {
    // Begin transaction
    $conn->begin_transaction();

    $updateStmt = $conn->prepare("UPDATE payment SET paymentStatus = 'Expired' WHERE memberID = ?");
    $updateStmt->bind_param("i", $memberID);
    $updateStmt->execute();
    $updateStmt->close();

    $insertStmt = $conn->prepare("INSERT INTO payment (paymentType, memberID, memberName, paymentDate, dueDate, amount, paymentStatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("sisssds", $paymentType, $memberID, $memberName, $paymentDate, $dueDate, $amount, $paymentStatus);

    if ($insertStmt->execute()) {
        $conn->commit(); // Commit the transaction
        ob_clean(); // Clear any unwanted output
        echo json_encode(["success" => true, "message" => "Schedule added successfully!"]);
    } else {
        throw new Exception("Failed to add schedule");
    }
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    if (isset($insertStmt)) $insertStmt->close();
    $conn->close();
    exit;
}
