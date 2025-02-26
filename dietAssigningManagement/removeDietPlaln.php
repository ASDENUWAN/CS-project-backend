<?php
header("Content-Type: application/json");
require '../config.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Ensure the request method is DELETE
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only DELETE requests are allowed"]);
    exit;
}
if (!isset($data['dietPlan_number'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

try {
    $dietPlan_number = $data['dietPlan_number'];
    $stmt = $conn->prepare("DELETE FROM dietplan WHERE dietPlan_number = ?");
    $stmt->bind_param("i", $dietPlan_number);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Diet plan deleted successfully"]);
    } else {
        throw new Exception("Failed to delete diet plan");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
