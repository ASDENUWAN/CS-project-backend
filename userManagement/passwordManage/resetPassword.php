<?php
header("Content-Type: application/json");
require '../../config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed."]);
    exit;
}

// Read incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['mail'], $data['newPassword'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and new password are required."]);
    exit;
}

$mail = trim($data['mail']);
$newPassword = trim($data['newPassword']);

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    // Check if email exists first
    $stmt = $conn->prepare("SELECT memID FROM members WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "No account found with this email."]);
        exit;
    }

    // Email exists, proceed to update password
    $update = $conn->prepare("UPDATE members SET password = ? WHERE mail = ?");
    $update->bind_param("ss", $hashedPassword, $mail);

    if ($update->execute()) {
        echo json_encode(["success" => true, "message" => "Password reset successfully!"]);
    } else {
        throw new Exception("Failed to reset password. Please try again.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($update)) $update->close();
    $conn->close();
}
