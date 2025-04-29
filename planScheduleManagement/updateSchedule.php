<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file

// Ensure the request method is PUT
if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only PUT requests are allowed"]);
    exit;
}

// Get raw JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input
if (!isset($data['memberSID'], $data['mpID'], $data['mplanName'], $data['scheduleDuration'], $data['sDate'], $data['eDate'], $data['scheduleStatus'])) {
    echo json_encode(["success" => false, "message" => "memberSID, mpID, mplanName, scheduleDuration, sDate, eDate, and scheduleStatus are required"]);
    exit;
}

$memberSID = trim($data['memberSID']);
$mpID = trim($data['mpID']);
$mplanName = trim($data['mplanName']);
$scheduleDuration = trim($data['scheduleDuration']);
$sDate = trim($data['sDate']);
$eDate = trim($data['eDate']);
$scheduleStatus = trim($data['scheduleStatus']);

try {
    // Update only mpID, mplanName, scheduleDuration, sDate, eDate, and scheduleStatus
    $stmt = $conn->prepare("UPDATE member_schedule SET mpID = ?, mplanName = ?, scheduleDuration = ?,  sDate = ?, eDate = ?, scheduleStatus = ? WHERE memberSID = ?");
    $stmt->bind_param("isssssi", $mpID, $mplanName, $scheduleDuration, $sDate, $eDate, $scheduleStatus, $memberSID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Schedule updated successfully"]);
    } else {
        throw new Exception("Failed to update schedule");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
