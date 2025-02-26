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

// Validate input fields
if (!isset($data['dietID'], $data['dietName'], $data['dietType'], $data['meals']) || !is_array($data['meals'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide dietID, dietName, dietType, and meals as an array."]);
    exit;
}

$dietID = intval($data['dietID']);
$dietName = trim($data['dietName']);
$dietType = trim($data['dietType']);
$meals = $data['meals']; // Array of meals

// Start transaction
$conn->begin_transaction();

try {
    // Update diet table
    $stmt = $conn->prepare("UPDATE diet SET dietName = ?, dietType = ? WHERE dietID = ?");
    $stmt->bind_param("ssi", $dietName, $dietType, $dietID);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update diet");
    }
    $stmt->close();

    // Delete existing meals for this dietID
    $stmt = $conn->prepare("DELETE FROM meal WHERE dietID = ?");
    $stmt->bind_param("i", $dietID);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete existing meals");
    }
    $stmt->close();

    // Insert new meals
    $stmt = $conn->prepare("INSERT INTO meal (dietID, mealType, foodItems) VALUES (?, ?, ?)");
    
    foreach ($meals as $meal) {
        if (!isset($meal['mealType'], $meal['foodItems'])) {
            throw new Exception("Invalid meal format");
        }

        $mealType = trim($meal['mealType']);
        $foodItems = trim($meal['foodItems']);

        $stmt->bind_param("iss", $dietID, $mealType, $foodItems);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert meal");
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(["success" => true, "message" => "Diet and meals updated successfully!"]);

} catch (Exception $e) {
    $conn->rollback(); // Rollback transaction on failure
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

// Close connections
$stmt->close();
$conn->close();
exit;
