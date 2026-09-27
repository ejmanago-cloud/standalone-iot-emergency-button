<?php

header('Content-Type: application/json');

$host = "localhost";
$dbname = "emergency_system";
$username = "root";
$password = "";

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode([
        "success" => false,
        "message" => "Emergency ID is missing"
    ]);
    exit;
}

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE emergencies
     SET read_status = 'READ'
     WHERE id = ?"
);

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Emergency marked as read"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to mark emergency as read"
    ]);
}

$stmt->close();
$conn->close();

?>