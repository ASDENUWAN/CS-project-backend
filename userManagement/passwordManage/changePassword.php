<?php
header("Content-Type: application/json");
require '../../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!isset($data['memID'], $data['newPassword'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input. Member ID and New Password are required."]);
    exit;
}

$memID = (int)$data['memID'];
$newPassword = trim($data['newPassword']);
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("UPDATE members SET password = ? WHERE memID = ?");
    $stmt->bind_param("si", $hashedPassword, $memID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Password updated successfully!"]);
    } else {
        throw new Exception("Failed to update password.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
