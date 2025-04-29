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

// Validate input fields
if (!isset($data['memberID'], $data['mpID'], $data['mplanName'], $data['scheduleDuration'], $data['sDate'], $data['eDate'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide all required fields."]);
    exit;
}

$memberID = trim($data['memberID']);
$mpID = trim($data['mpID']);
$mplanName = trim($data['mplanName']);
$scheduleDuration = trim($data['scheduleDuration']);
$sDate = trim($data['sDate']);
$eDate = trim($data['eDate']);
$scheduleStatus = 'Active';

try {
    // Begin transaction
    $conn->begin_transaction();

    // Step 1: Expire existing schedules for this member
    $updateStmt = $conn->prepare("UPDATE member_schedule SET scheduleStatus = 'Expired' WHERE memberID = ?");
    $updateStmt->bind_param("i", $memberID);
    $updateStmt->execute();
    $updateStmt->close();

    // Step 2: Insert new schedule
    $insertStmt = $conn->prepare("INSERT INTO member_schedule (memberID, mpID, mplanName, scheduleDuration, sDate, eDate, scheduleStatus) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("iisssss", $memberID, $mpID, $mplanName, $scheduleDuration, $sDate, $eDate, $scheduleStatus);

    if ($insertStmt->execute()) {
        $conn->commit(); // Commit the transaction
        ob_clean(); // Clear any unwanted output
        echo json_encode(["success" => true, "message" => "Schedule added successfully!"]);
    } else {
        throw new Exception("Failed to add schedule");
    }
} catch (Exception $e) {
    $conn->rollback(); // Rollback transaction on error
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    if (isset($insertStmt)) $insertStmt->close();
    $conn->close();
    exit;
}
