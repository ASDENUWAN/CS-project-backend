<?php
header("Content-Type: application/json");
require '../config.php'; // Ensure this file exists and has the correct DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['dietID']) && !isset($data['dietName'])) {
        echo json_encode(["success" => false, "message" => "Diet Name or ID is required"]);
        exit;
    }

    if (!empty($data['dietName'])) {
        $dietName = $data['dietName'];
        $stmt = $conn->prepare("SELECT dietID FROM diet WHERE dietName = ?");
        $stmt->bind_param("s", $dietName);
    } elseif (!empty($data['dietID'])) {
        $dietID = $data['dietID'];
        $stmt = $conn->prepare("SELECT dietName FROM diet WHERE dietID = ?");
        $stmt->bind_param("i", $dietID);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "dietData" => $row]);
    } else {
        echo json_encode(["success" => false, "message" => "Diet not found"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}

