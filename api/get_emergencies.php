<?php

header('Content-Type: application/json');

$host = "localhost";
$dbname = "emergency_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$sql = "SELECT * FROM emergencies ORDER BY created_at DESC";

$result = $conn->query($sql);

$emergencies = [];

while ($row = $result->fetch_assoc()) {
    $emergencies[] = $row;
}

echo json_encode([
    "success" => true,
    "emergencies" => $emergencies
]);

$conn->close();

?>