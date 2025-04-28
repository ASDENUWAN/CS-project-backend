<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file
ob_clean();

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

// Fixed member ID
$memberID = 11;

try {
    // Fetch payments for the given memberID from the database
    $stmt = $conn->prepare("SELECT paymentID, paymentType, paymentDate, dueDate, amount, paymentStatus 
                            FROM payment 
                            WHERE memberID = ? 
                            ORDER BY paymentID DESC");
    $stmt->bind_param("i", $memberID);
    $stmt->execute();
    $result = $stmt->get_result();

    $payments = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        echo json_encode(["success" => true, "payments" => $payments]);
    } else {
        throw new Exception("No payments found for member ID 11.");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
