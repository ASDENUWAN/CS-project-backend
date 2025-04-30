<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

try {
    $sql = "
        SELECT 
            SUM(CASE WHEN adRole = 'Manager' THEN 1 ELSE 0 END) AS totalManagers,
            SUM(CASE WHEN adRole = 'Operator' THEN 1 ELSE 0 END) AS totalOperators,
            SUM(CASE WHEN adRole = 'Trainer' THEN 1 ELSE 0 END) AS totalTrainers,
            COUNT(*) AS totalUsers
        FROM admin
    ";

    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    echo json_encode([
        "success" => true,
        "totalManagers" => (int)$row['totalManagers'],
        "totalOperators" => (int)$row['totalOperators'],
        "totalTrainers" => (int)$row['totalTrainers'],
        "totalUsers" => (int)$row['totalUsers']
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $conn->close();
    exit;
}
