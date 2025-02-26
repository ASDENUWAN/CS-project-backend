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

// Check if required fields are provided
if (!isset($data["wpID"], $data["wpName"])) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

// Get the input data
$wpID = isset($data['wpID']) ? $data['wpID'] : null;
$wpName = isset($data['wpName']) ? $data['wpName'] : null;
$exercises = isset($data['exercises']) ? $data['exercises'] : [];

if (empty($wpID) || empty($wpName)) {
    echo json_encode(["success" => false, "message" => "Invalid workout plan ID or name."]);
    exit;
}

try {
    // Start a transaction to ensure data integrity
    //$pdo->beginTransaction();

    // 1. Delete old exercises associated with the workout plan
    $stmt = $conn->prepare("DELETE FROM exercise WHERE wpID = ?");
    $stmt->execute([$wpID]);

    // 2. Insert the new exercises
    foreach ($exercises as $exercise) {
        // Prepare the exercise data
        $exName = $exercise['exName'];
        $exType = $exercise['exType'];
        $s1 = $exercise['s1'];
        $s2 = $exercise['s2'];
        $s3 = $exercise['s3'];
        $s4 = $exercise['s4'];

        // Insert exercise
        $stmt = $conn->prepare("INSERT INTO exercise (wpID, exName, exType, s1, s2, s3, s4) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$wpID, $exName, $exType, $s1, $s2, $s3, $s4]);
    }

    // 3. Update the workout plan name
    $stmt = $conn->prepare("UPDATE workout_plan SET wpName = ? WHERE wpID = ?");
    $stmt->execute([$wpName, $wpID]);

    // Commit the transaction


    echo json_encode(["success" => true, "message" => "Workout plan updated successfully."]);
} catch (Exception $e) {
    // Rollback the transaction if something goes wrong
    $conn->rollBack();
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
