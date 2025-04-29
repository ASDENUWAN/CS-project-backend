<?php
header("Content-Type: application/json");
require '../config.php';

$json = file_get_contents("php://input");
$data = json_decode($json, true);

// Ensure the request method is DELETE
if ($_SERVER["REQUEST_METHOD"] !== "DELETE") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only DELETE requests are allowed"]);
    exit;
}
if (!isset($data['dietID'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$dietID = $data['dietID'];
try{
$stmt = $conn->prepare("DELETE FROM diet WHERE dietID = ?");
$stmt->bind_param("i", $dietID);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Diet deleted successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete diet"]);
}
} catch(Exception $e){
   echo json_encode(["success" => false, "message" => $e->getMessage()]);
} finally{

$stmt->close();
$conn->close();
exit;
}
