<?php
header("Content-Type: application/json");
require '../../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$mail = trim($data['mail'] ?? '');
$code = trim($data['code'] ?? '');

try {
    $stmt = $conn->prepare("SELECT code, expires_at FROM reset_codes WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row || $row['code'] !== $code) {
        echo json_encode(["success" => false, "message" => "Invalid code."]);
        exit;
    }

    if (strtotime($row['expires_at']) < time()) {
        echo json_encode(["success" => false, "message" => "Code expired."]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Code verified."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
