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
if (!isset($data['paymentID'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$paymentID = $data['paymentID'];
try {
    $stmt = $conn->prepare("DELETE FROM payment WHERE paymentID = ?");
    $stmt->bind_param("i", $paymentID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Payment deleted successfully"]);
    } else {
        throw new Exception("Failed to delete payment");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
