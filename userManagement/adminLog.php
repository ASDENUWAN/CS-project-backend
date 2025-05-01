<?php
header("Content-Type: application/json");
session_start();
require '../config.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed."]);
    exit;
}

// Get raw JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input fields
if (!isset($data['adUserName'], $data['adPass'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide username and password."]);
    exit;
}

$adUserName = trim($data['adUserName']);
$password = trim($data['adPass']);



// Prepare and execute SQL statement
$stmt = $conn->prepare("SELECT adID, adUserName, adRole, adPass FROM admin WHERE adUserName = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error. Please try again later."]);
    exit;
}
$stmt->bind_param("s", $adUserName);
$stmt->execute();
$stmt->store_result();

// Check if username exists
if ($stmt->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid username or password."]);
    $stmt->close();
    $conn->close();
    exit;
}

// Bind the result variables
$stmt->bind_result($adminID, $username, $role, $hashedPassword);
$stmt->fetch();

// Verify the password
if (!password_verify($password, $hashedPassword)) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid email or password."]);
    $stmt->close();
    $conn->close();
    exit;
}

// Set session variables on successful login
$_SESSION['admin_id'] = $adminID;
$_SESSION['admin_username'] = $username;
$_SESSION['admin_role'] = $role;

// Respond with success message
echo json_encode([
    "success" => true,
    "message" => "Admin login successful!",

]);

$stmt->close();
$conn->close();
exit;
