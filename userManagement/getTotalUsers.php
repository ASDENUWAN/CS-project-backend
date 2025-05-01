<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file

// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

try {
    // Fetch total number of users from the members table
    $stmt = $conn->prepare("SELECT COUNT(*) AS totalUsers FROM members");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totalUsers = $row['totalUsers'];

    echo json_encode(["success" => true, "totalUsers" => $totalUsers]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
