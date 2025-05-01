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
if (!isset($data['mail'], $data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide email and password."]);
    exit;
}

$email = trim($data['mail']);
$password = trim($data['password']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email format."]);
    exit;
}

// Prepare and execute SQL statement
$stmt = $conn->prepare("SELECT memID, memName, password FROM members WHERE mail = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error. Please try again later."]);
    exit;
}
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// Check if email exists
if ($stmt->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid email or password."]);
    $stmt->close();
    $conn->close();
    exit;
}

// Bind the result variables
$stmt->bind_result($id, $name, $hashedPassword);
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
$_SESSION['user_id'] = $id;
$_SESSION['mem_name'] = $name;

// Respond with success message
echo json_encode([
    "success" => true,
    "message" => "Login successful!",
]);

$stmt->close();
$conn->close();
exit;
