<?php
header("Content-Type: application/json");
require '../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['wpName']) && !isset($data['wpID'])) {
        echo json_encode(["success" => false, "message" => "Plan name or ID is required"]);
        exit;
    }

    if (isset($data['wpName'])) {
        $wpName = $data['wpName'];
        $stmt = $conn->prepare("SELECT wpID, wpName FROM workout_plan WHERE wpName = ?");
        $stmt->bind_param("s", $wpName);
    } else {
        $wpID = $data['wpID'];
        $stmt = $conn->prepare("SELECT wpID, wpName FROM workout_plan WHERE wpID = ?");
        $stmt->bind_param("i", $wpID);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "planData" => $row]);
    } else {
        echo json_encode(["success" => false, "message" => "Plan not found"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
