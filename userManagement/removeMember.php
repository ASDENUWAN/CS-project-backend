<?php
header("Content-Type: application/json");
require '../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!isset($data['memID'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Member ID is required."]);
    exit;
}

$memID = (int)$data['memID'];

try {
    $stmt = $conn->prepare("DELETE FROM members WHERE memID = ?");
    $stmt->bind_param("i", $memID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Member deleted successfully!"]);
    } else {
        throw new Exception("Failed to delete member");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
