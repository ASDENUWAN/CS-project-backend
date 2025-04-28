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

// Get JSON input
$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Validate input
if (empty($data["memberID"]) && empty($data["memberName"])) {
    echo json_encode(["success" => false, "message" => "Member ID or Member Name is required"]);
    exit;
}

// Prepare SQL query dynamically based on provided input
$query = "SELECT paymentID, memberID, memberName, paymentType, paymentDate, dueDate, amount, paymentStatus FROM payment WHERE ";
$params = [];
$types = "";

if (!empty($data["memberID"])) {
    $query .= "memberID = ? ";
    $params[] = $data["memberID"];
    $types .= "i";
}

if (!empty($data["memberName"])) {
    if (!empty($params)) {
        $query .= "OR ";
    }
    $query .= "memberName LIKE ? ";
    $params[] = "%" . $data["memberName"] . "%"; // Partial match
    $types .= "s";
}

try {
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters dynamically
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $payments = [];

    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }

    if (count($payments) > 0) {
        echo json_encode(["success" => true, "payments" => $payments]);
    } else {
        throw new Exception("No payments found matching the criteria");
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
