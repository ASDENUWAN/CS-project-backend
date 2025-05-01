<?php
header("Content-Type: application/json");
require '../config.php'; // Ensure this file exists and has the correct DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['memberNIC']) && !isset($data['memberID'])) {
        echo json_encode(["success" => false, "message" => "Member NIC or ID is required"]);
        exit;
    }

    if (!empty($data['memberNIC'])) {
        $memberNIC = $data['memberNIC'];
        $stmt = $conn->prepare("SELECT memID FROM members WHERE memNIC = ?");
        $stmt->bind_param("s", $memberNIC);
    } elseif (!empty($data['memberID'])) {
        $memberID = $data['memberID'];
        $stmt = $conn->prepare("SELECT memNIC FROM members WHERE memID = ?");
        $stmt->bind_param("i", $memberID);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(["success" => true, "memberData" => $row]);
    } else {
        echo json_encode(["success" => false, "message" => "Member not found"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}