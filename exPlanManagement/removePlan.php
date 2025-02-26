<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file

// Ensure the request method is DELETE
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only DELETE requests are allowed"]);
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

$conn->begin_transaction(); // Start transaction

try {
    // Delete exercises associated with the plan
    $stmt = $conn->prepare("DELETE FROM exercise WHERE wpID = ?");
    $stmt->bind_param("i", $wpID);
    $stmt->execute();
    $stmt->close();

    // Delete the workout plan
    $stmt = $conn->prepare("DELETE FROM workout_plan WHERE wpID = ?");
    $stmt->bind_param("i", $wpID);
    $stmt->execute();
    $stmt->close();

    $conn->commit(); // Commit transaction

    echo json_encode(["success" => true, "message" => "Workout plan and associated exercises deleted successfully!"]);
} catch (Exception $e) {
    $conn->rollback(); // Rollback transaction on failure
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to delete workout plan and exercises", "error" => $e->getMessage()]);
}

$conn->close();
exit;
