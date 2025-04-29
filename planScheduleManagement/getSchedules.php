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
    // Query to fetch all schedules
    $sql = "SELECT memberSID, memberID, mpID, mplanName,scheduleDuration, sDate, eDate, scheduleStatus FROM member_schedule";
    $result = $conn->query($sql);

    $schedules = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }
        echo json_encode(["success" => true, "schedules" => $schedules]);
    } else {
        throw new Exception("No schedules found");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $conn->close();
    exit;
}
