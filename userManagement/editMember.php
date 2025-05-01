<?php
header("Content-Type: application/json");
require '../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests are allowed"]);
    exit;
}

$json = file_get_contents("php://input");
$data = json_decode($json, true);

if (!isset($data['memID'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Member ID is required."]);
    exit;
}

$memID = (int)$data['memID'];
$memName = trim($data['memName'] ?? '');
$memNIC = trim($data['memNIC'] ?? '');
$mail = trim($data['mail'] ?? '');
$mobile = trim($data['mobile'] ?? '');
$address = trim($data['address'] ?? '');
$gender = trim($data['gender'] ?? '');
$age = (int)($data['age'] ?? 0);
$height = (float)($data['height'] ?? 0);
$weight = (float)($data['weight'] ?? 0);

try {
    $stmt = $conn->prepare("UPDATE members SET memName=?, memNIC=?,mail=?, mobile=?, address=?, gender=?, age=?, height=?, weight=? WHERE memID=?");
    $stmt->bind_param("ssssssidii", $memName, $memNIC, $mail, $mobile, $address, $gender, $age, $height, $weight, $memID);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Member updated successfully!"]);
    } else {
        throw new Exception("Failed to update member");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally {
    $stmt->close();
    $conn->close();
    exit;
}
