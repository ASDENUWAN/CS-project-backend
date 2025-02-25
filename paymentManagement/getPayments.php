<?php
header("Content-Type: application/json");
require '../config.php'; // Adjust path if needed

// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}
// Query to fetch all employees
$sql = "SELECT paymentID, paymentType, memberID, memberName, paymentDate, dueDate, amount, paymentStatus FROM payment";
$result = $conn->query($sql);

$payments = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
    echo json_encode(["success" => true, "payments" => $payments]);
} else {
    echo json_encode(["success" => false, "message" => "No payments found"]);
}

$conn->close();
exit;