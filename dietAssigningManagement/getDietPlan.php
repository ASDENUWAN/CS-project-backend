<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file
ob_clean();

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

// Get JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input
if (!isset($data["dietPlan_number"])) {
    echo json_encode(["success" => false, "message" => "Diet plan ID is required"]);
    exit;
}

$dietPlan_number = $data["dietPlan_number"];

// Fetch dietPlan details from database
$stmt = $conn->prepare("SELECT dietPlan_number, memberID, dietID, startDate, endDate, dpStatus, height, weightKG FROM dietplan WHERE dietPlan_number = ?");
$stmt->bind_param("i", $dietPlan_number);
$stmt->execute();
$result = $stmt->get_result();

try {
    if ($result->num_rows > 0) {
        $dietPlan = $result->fetch_assoc();
        echo json_encode(["success" => true, "dietPlan" => $dietPlan]);
    } else {
        throw new Exception("Diet plan not found");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
