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
if (!isset($data["dietPlan_number"], $data["memberID"], $data["dietID"], $data["startDate"], $data["endDate"], $data["dpStatus"], $data["height"], $data["weightKG"])) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

$dietPlan_number = intval($data["dietPlan_number"]);
$memberID = intval($data['memberID']);
$dietID = intval($data['dietID']);
$startDate = trim($data['startDate']);
$endDate = trim($data['endDate']);
$dpStatus = trim($data['dpStatus']);
$height = floatval($data['height']);
$weightKG = floatval($data['weightKG']);

// Update diet plan details in the database
$stmt = $conn->prepare("UPDATE dietplan SET memberID = ?, dietID = ?, startDate = ?, endDate = ?, dpStatus = ? , height = ?, weightKG = ? WHERE dietPlan_number = ?");
$stmt->bind_param("iisssddi", $memberID, $dietID, $startDate, $endDate, $dpStatus, $height, $weightKG, $dietPlan_number);

try {
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Diet plan updated successfully"]);
    } else {
        throw new Exception("Failed to update diet plan");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}



