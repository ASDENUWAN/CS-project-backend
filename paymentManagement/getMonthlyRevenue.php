<?php
header("Content-Type: application/json");
require '../config.php'; // Include your database configuration

// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

try {
    // Get the current year and month
    $currentYear = date('Y');
    $currentMonth = date('m');

    // Query to fetch total payments for the current month
    $sql = "SELECT SUM(amount) AS total_income, paymentDate 
            FROM payment 
            WHERE YEAR(paymentDate) = '$currentYear' 
            AND MONTH(paymentDate) = '$currentMonth' 
            GROUP BY DAY(paymentDate)
            ORDER BY paymentDate ASC";

    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $data = [];
    if ($result->num_rows > 0) {
        // Output each row as an associative array
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'date' => $row['paymentDate'],
                'amount' => (float)$row['total_income'],
            ];
        }
        // Return the data as JSON
        echo json_encode(["success" => true, "data" => $data]);
    } else {
        throw new Exception("No payments found for the current month.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    // Close the database connection
    $conn->close();
    exit;
}
