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
if (!isset($data['memberID'], $data['mpID'], $data['mplanName'], $data['sDate'], $data['eDate'], $data['scheduleStatus'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide all required fields."]);
    exit;
}


$memberID = trim($data['memberID']);
$mpID = trim($data['mpID']);
$mplanName= trim($data['mplanName']);
$sDate = trim($data['sDate']);
$eDate = trim($data['eDate']);
$scheduleStatus = trim($data['scheduleStatus']);

try{
    // Prepare SQL statement to insert schedule data
    $stmt = $conn->prepare("INSERT INTO member_schedule (memberID, mpID, mplanName, sDate, eDate, scheduleStatus) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $memberID, $mpID, $mplanName, $sDate, $eDate, $scheduleStatus);

    if ($stmt->execute()) {
        ob_clean(); // Clear any unwanted output
        echo json_encode(["success" => true, "message" => "Schedule added successfully!"]);
    } else {
       throw new Exception("Failed to add schedule");
    }
} catch(Exception $e){
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally{
    $stmt->close();
    $conn->close();
    exit;
}