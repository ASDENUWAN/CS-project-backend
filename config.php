<?php
// Database configuration

$host = "localhost:3308";

$dbname = "fitnessdb";

$username = "root";

$password = "";

// Create a connection to the database

$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection is successful

if ($conn->connect_error) {

    die(json_encode(["message" => "Database connection failed: " . $conn->connect_error]));
}
