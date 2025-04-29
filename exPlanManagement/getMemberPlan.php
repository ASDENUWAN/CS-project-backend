<?php
header("Content-Type: application/json");
require '../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['memberID'])) {
    echo json_encode(["success" => false, "message" => "Member ID required"]);
    exit;
}

$memberID = intval($data['memberID']);
$response = ["success" => true];

try {
    // Active plan
    $stmt = $conn->prepare("SELECT ms.mpID, wp.wpName, ms.sDate, ms.eDate FROM member_schedule ms 
                            JOIN workout_plan wp ON ms.mpID = wp.wpID 
                            WHERE ms.memberID = ? AND ms.scheduleStatus = 'Active' LIMIT 1");
    $stmt->bind_param("i", $memberID);
    $stmt->execute();
    $activeResult = $stmt->get_result();

    if ($activeResult->num_rows > 0) {
        $activePlan = $activeResult->fetch_assoc();
        $mpID = $activePlan['mpID'];

        // Get exercises for active plan
        $stmt = $conn->prepare("SELECT * FROM exercise WHERE wpID = ?");
        $stmt->bind_param("i", $mpID);
        $stmt->execute();
        $exerciseResult = $stmt->get_result();
        $exercises = [];
        while ($row = $exerciseResult->fetch_assoc()) {
            $exercises[] = $row;
        }

        $response['activePlan'] = [
            "sDate" => $activePlan['sDate'],
            "eDate" => $activePlan['eDate'],
            "exercises" => $exercises
        ];
    } else {
        $response['activePlan'] = null;
    }

    // Expired plans
    $stmt = $conn->prepare("SELECT ms.sDate, ms.eDate,ms.mpID FROM member_schedule ms 
                            WHERE ms.memberID = ? AND ms.scheduleStatus = 'Expired'");
    $stmt->bind_param("i", $memberID);
    $stmt->execute();
    $expiredResult = $stmt->get_result();

    $expiredPlans = [];
    while ($row = $expiredResult->fetch_assoc()) {
        $expiredPlans[] = $row;
    }

    $response['expiredPlans'] = $expiredPlans;

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Server error", "error" => $e->getMessage()]);
}

$conn->close();
