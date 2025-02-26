<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file

// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

// Get JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input
if (!isset($data["dietID"])) {
    echo json_encode(["success" => false, "message" => "Diet ID is required"]);
    exit;
}

$dietID =$data["dietID"]; // Convert to integer for security

// Fetch diet details
$dietQuery = $conn->prepare("SELECT dietID, dietName, dietType FROM diet WHERE dietID = ?");
$dietQuery->bind_param("i", $dietID);
$dietQuery->execute();
$dietResult = $dietQuery->get_result();

if ($dietResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Diet not found"]);
    exit;
}

$diet = $dietResult->fetch_assoc();

// Fetch associated meals
$mealQuery = $conn->prepare("SELECT mealType, foodItems FROM meal WHERE dietID = ?");
$mealQuery->bind_param("i", $dietID);
$mealQuery->execute();
$mealResult = $mealQuery->get_result();

$meals = [];
while ($row = $mealResult->fetch_assoc()) {
    $meals[] = $row;
}

// Close connections
$dietQuery->close();
$mealQuery->close();
$conn->close();

// Return response
echo json_encode([
    "success" => true,
    "diet" => $diet,
    "meals" => $meals
]);
?>
