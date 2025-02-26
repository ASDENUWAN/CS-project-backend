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
if (!isset($data['memName'], $data['mail'], $data['mobile'], $data['age'], $data['address'], $data['gender'], $data['password'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Please provide all required fields."]);
    exit;
}

$memName = trim($data['memName']);
$mail = trim($data['mail']);
$mobile = trim($data['mobile']);
$address = trim($data['address']);
$gender = trim($data['gender']);
$age = trim($data['age']);
$height = (float)$data['height'];
$weight = (float)$data['weight'];
$pass = trim($data['password']);
$password = password_hash($pass, PASSWORD_DEFAULT);

try {
    // Prepare SQL statement to insert member data
    $stmt = $conn->prepare("INSERT INTO members (memName, mail, age, mobile, address, gender, height, weight, password, regDate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssissssss", $memName, $mail, $age, $mobile, $address, $gender, $height, $weight, $password);

    if ($stmt->execute()) {
        ob_clean(); // Clear any unwanted output
        echo json_encode(["success" => true, "message" => "Member added successfully!"]);
    } else {
        throw new Exception("Failed to add member");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
