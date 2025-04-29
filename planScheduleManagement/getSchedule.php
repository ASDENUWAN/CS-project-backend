<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file
ob_clean();
// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only Post requests are allowed"]);
    exit;
}

// Get JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input
if (!isset($data["memberSID"])) {
    echo json_encode(["success" => false, "message" => "MemberS ID is required"]);
    exit;
}

$memberSID = $data["memberSID"];

try {
    // Fetch schedule details from database
    $stmt = $conn->prepare("SELECT memberSID, memberID, mpID, mplanName,scheduleDuration, sDate, eDate, scheduleStatus FROM member_schedule WHERE memberSID = ?");
    $stmt->bind_param("i", $memberSID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $schedule = $result->fetch_assoc();
        echo json_encode(["success" => true, "schedule" => $schedule]);
    } else {
        throw new Exception("Failed to get schedule");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
