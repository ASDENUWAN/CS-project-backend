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
if (!isset($data['wpName']) || !isset($data['exercises']) || !is_array($data['exercises'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide all required fields."]);
    exit;
}

$wpName = trim($data['wpName']);
$exercises = $data['exercises']; // Expecting an array of exercises

$conn->begin_transaction(); // Start transaction

try {
    // Insert into workout_plan table
    $stmt = $conn->prepare("INSERT INTO workout_plan (wpName) VALUES (?)");
    $stmt->bind_param("s", $wpName);
    $stmt->execute();
    $wpID = $stmt->insert_id; // Get the last inserted ID
    $stmt->close();

    // Insert exercises related to this plan
    $stmt = $conn->prepare("INSERT INTO exercise (wpID, exName, exType, s1, s2, s3, s4) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($exercises as $exercise) {
        if (!isset($exercise['exName'], $exercise['exType'], $exercise['s1'], $exercise['s2'], $exercise['s3'], $exercise['s4'])) {
            throw new Exception("Invalid exercise format");
        }

        $exName = trim($exercise['exName']);
        $exType = trim($exercise['exType']); // New field
        $s1 = $exercise['s1'];
        $s2 = $exercise['s2'];
        $s3 = $exercise['s3'];
        $s4 = $exercise['s4'];

        $stmt->bind_param("issiiii", $wpID, $exName, $exType, $s1, $s2, $s3, $s4);
        $stmt->execute();
    }

    $stmt->close();
    $conn->commit(); // Commit transaction

    echo json_encode(["success" => true, "message" => "Plan and exercises added successfully!"]);
} catch (Exception $e) {
    $conn->rollback(); // Rollback transaction on failure
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to add Plan and exercises", "error" => $e->getMessage()]);
}

$conn->close();
exit;
