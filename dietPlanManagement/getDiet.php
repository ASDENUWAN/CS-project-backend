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
    // Fetch all diets
    $query = "SELECT dietID, dietName, dietType FROM diet";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $diets = [];
    while ($row = $result->fetch_assoc()) {
        $diets[] = $row;
    }

    // Return response
    echo json_encode([
        "success" => true,
        "diets" => $diets
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    // Close connection
    $conn->close();
}
?>
