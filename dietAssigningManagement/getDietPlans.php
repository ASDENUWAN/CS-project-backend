<?php
header("Content-Type: application/json");
require '../config.php'; // Adjust path if needed

// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}
// Query to fetch all dietPlans
$sql = "SELECT dietPlan_number, memberID, dietID, startDate, endDate, dpStatus, height, weightKG FROM dietplan";
$result = $conn->query($sql);

$dietPlans = [];

try {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $dietPlans[] = $row;
        }
        echo json_encode(["success" => true, "dietPlans" => $dietPlans]);
    } else {
        throw new Exception("No diet plans found");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $conn->close();
    exit;
}
