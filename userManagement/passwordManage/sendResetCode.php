<?php
header("Content-Type: application/json");
require '../../config.php'; // your database connection
require __DIR__ . '/../../vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Random Code Generator ---
function generateCode($length = 6)
{
    return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// --- Validate Request Method ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST requests allowed."]);
    exit;
}

// --- Get Request Data ---
$data = json_decode(file_get_contents("php://input"), true);

$mail = trim($data['mail'] ?? '');

if (empty($mail)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email required."]);
    exit;
}

try {
    // --- Check if Email Exists ---
    $stmt = $conn->prepare("SELECT memID FROM members WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo json_encode(["success" => false, "message" => "Email not found."]);
        exit;
    }

    // --- Generate Verification Code ---
    $code = generateCode();
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // --- Insert or Update the Code in reset_codes Table ---
    $insert = $conn->prepare("REPLACE INTO reset_codes (mail, code, expires_at) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $mail, $code, $expiry);
    $insert->execute();

    // --- Setup PHPMailer ---
    $email = new PHPMailer(true);

    $email->isSMTP();
    $email->Host = 'smtp.gmail.com';
    $email->SMTPAuth = true;
    $email->Username = 'cscprojectsusj@gmail.com';
    $email->Password = 'lfldmamoyattpahk';
    $email->SMTPSecure = 'ssl';
    $email->Port = 465;


    $email->setFrom('cscprojectsusj@gmail.com', 'fitness');
    $email->addAddress($mail);
    $email->Subject = 'Password Reset Code';
    $email->Body = "Your password reset verification code is: $code";

    $email->send();

    // --- Success Response ---
    echo json_encode(["success" => true, "message" => "Verification code sent successfully. Please check your email."]);
} catch (Exception $e) {
    // --- Catch any errors ---
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to send verification code: " . $e->getMessage()]);
}
