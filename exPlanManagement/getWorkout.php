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
    // Fetch all workout plans
    $query = "SELECT wpID, wpName FROM workout_plan";
    $result = $conn->query($query);

    $plans = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = [
                "wpID" => $row["wpID"],
                "wpName" => $row["wpName"]
            ];
        }
    }

    echo json_encode(["success" => true, "plans" => $plans]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to retrieve workout plans", "error" => $e->getMessage()]);
}

$conn->close();
exit;
