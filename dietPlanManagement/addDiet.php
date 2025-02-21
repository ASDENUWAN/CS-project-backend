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
if (!isset($data['dietName'], $data['dietType'], $data['meals']) || !is_array($data['meals'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide dietName, dietType, and meals as an array."]);
    exit;
}

$dietName = trim($data['dietName']);
$dietType = trim($data['dietType']);
$meals = $data['meals']; // Array of meals

// Start transaction to ensure both inserts succeed
$conn->begin_transaction();

try {
    // Insert into diet table
    $stmt = $conn->prepare("INSERT INTO diet (dietName, dietType) VALUES (?, ?)");
    $stmt->bind_param("ss", $dietName, $dietType);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert diet");
    }

    // Get the generated dietID
    $dietID = $conn->insert_id;
    $stmt->close();

    // Insert meals related to the diet
    $stmt = $conn->prepare("INSERT INTO meal (dietID, mealType, foodItems) VALUES (?, ?, ?)");
    
    foreach ($meals as $meal) {
        if (!isset($meal['mealType'], $meal['foodItems'])) {
            throw new Exception("Invalid meal format");
        }
        
        $mealType = trim($meal['mealType']);
        $foodItems = trim($meal['foodItems']); // Assuming a comma-separated string

        $stmt->bind_param("iss", $dietID, $mealType, $foodItems);
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert meal");
        }
    }

    // Commit transaction if all inserts succeed
    $conn->commit();

    ob_clean(); // Clear any unwanted output
    echo json_encode(["success" => true, "message" => "Diet and meals added successfully!"]);

} catch (Exception $e) {
    $conn->rollback(); // Rollback transaction in case of failure
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

// Close statement and connection
$stmt->close();
$conn->close();
exit;
