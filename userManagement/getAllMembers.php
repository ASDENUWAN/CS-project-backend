<?php
header("Content-Type: application/json");
require '../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

try {
    $result = $conn->query("SELECT memID, memName, memNIC, mail, age, mobile, address, gender, height, weight, regDate FROM members ORDER BY regDate DESC");

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }

    echo json_encode(["success" => true, "data" => $members]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $conn->close();
    exit;
}
