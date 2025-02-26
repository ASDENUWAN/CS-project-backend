<?php
header("Content-Type: application/json");
require '../config.php'; // Adjust path if needed

// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

try {
    // Query to fetch all payments
    $sql = "SELECT paymentID, paymentType, memberID, memberName, paymentDate, dueDate, amount, paymentStatus FROM payment ORDER BY paymentID DESC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $payments = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        echo json_encode(["success" => true, "payments" => $payments]);
    } else {
        throw new Exception("No payments found.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $conn->close();
    exit;
}
