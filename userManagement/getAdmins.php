<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file
ob_clean();

// Ensure the request method is GET (since we're retrieving data)
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

try {
    // Fetch all admin details except for password from the database
    $stmt = $conn->prepare("SELECT adUserName, adName, adMail, adPhone, adRole FROM admin");
    $stmt->execute();
    $result = $stmt->get_result();

    $admins = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
        echo json_encode(["success" => true, "admins" => $admins]);
    } else {
        throw new Exception("No admin records found.");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
