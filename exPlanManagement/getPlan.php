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
if (!isset($data['wpID'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide wpID."]);
    exit;
}

$wpID = intval($data['wpID']);

try {
    // Retrieve workout plan details
    $stmt = $conn->prepare("SELECT * FROM workout_plan WHERE wpID = ?");
    $stmt->bind_param("i", $wpID);
    $stmt->execute();
    $planResult = $stmt->get_result();
    $stmt->close();

    if ($planResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Workout plan not found."]);
        exit;
    }

    $plan = $planResult->fetch_assoc();

    // Retrieve exercises associated with the plan
    $stmt = $conn->prepare("SELECT * FROM exercise WHERE wpID = ?");
    $stmt->bind_param("i", $wpID);
    $stmt->execute();
    $exercisesResult = $stmt->get_result();
    $stmt->close();

    $exercises = [];
    while ($row = $exercisesResult->fetch_assoc()) {
        $exercises[] = $row;
    }

    echo json_encode(["success" => true, "workout_plan" => $plan, "exercises" => $exercises]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to retrieve workout plan and exercises", "error" => $e->getMessage()]);
}

$conn->close();
exit;
