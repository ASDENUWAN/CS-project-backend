<?php
header("Content-Type: application/json");
require '../config.php'; // Ensure this file exists and has the correct DB connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['memNIC']) && !isset($data['memID'])) {
        echo json_encode(["success" => false, "message" => "Member nic or ID is required"]);
        exit;
    }

    if (isset($data['memNIC'])) {
        $memNIC = $data['memNIC'];
        $stmt = $conn->prepare("SELECT memID, memName FROM members WHERE memNIC = ?");
        $stmt->bind_param("s", $memNIC);
    } else {
        $memID = $data['memID'];
        $stmt = $conn->prepare("SELECT memNIC, memName FROM members WHERE memID = ?");
        $stmt->bind_param("i", $memID);
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
