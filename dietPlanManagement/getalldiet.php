<?php
header("Content-Type: application/json");
require '../config.php'; // Database connection file

// Ensure the request method is GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only GET requests are allowed"]);
    exit;
}

// Query to fetch all diet plans along with meals
$sql = "
    SELECT 
        d.dietID,
        d.dietName,
        d.dietType,
        GROUP_CONCAT(CASE WHEN m.mealType = 'Breakfast' THEN m.foodItems END) AS Breakfast,
        GROUP_CONCAT(CASE WHEN m.mealType = 'Lunch' THEN m.foodItems END) AS Lunch,
        GROUP_CONCAT(CASE WHEN m.mealType = 'Dinner' THEN m.foodItems END) AS Dinner
    FROM 
        diet d
    LEFT JOIN 
        meal m ON d.dietID = m.dietID
    GROUP BY 
        d.dietID
";

$result = $conn->query($sql);

$diets = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $diets[] = $row;
    }
    echo json_encode(["success" => true, "diets" => $diets]);
} else {
    echo json_encode(["success" => false, "message" => "No diet plans found"]);
}

$conn->close();
exit;
?>
