<?php

header('Content-Type: application/json');

$host = "localhost";
$dbname = "emergency_system";
$username = "root";
$password = "";

// Get data from URL
$barangay_id = $_GET['barangay_id'] ?? '';
$type = $_GET['type'] ?? '';
$latitude = $_GET['latitude'] ?? '';
$longitude = $_GET['longitude'] ?? '';

// Allowed emergency types
$allowed_types = ['POLICE', 'FIRE', 'MEDICAL'];

// Check emergency type
if (!in_array(strtoupper($type), $allowed_types)) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid emergency type"
    ]);

    exit;
}

// Check required data
if (
    empty($barangay_id) ||
    empty($latitude) ||
    empty($longitude)
) {

    echo json_encode([
        "success" => false,
        "message" => "Missing required information"
    ]);

    exit;
}

// Connect to MySQL
$conn = new mysqli(
    $host,
    $username,
    $password,
    $dbname
);

// Check database connection
if ($conn->connect_error) {

    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error
    ]);

    exit;
}

// Convert emergency type to uppercase
$type = strtoupper($type);

// Insert emergency
$stmt = $conn->prepare(
    "INSERT INTO emergencies
    (barangay_id, emergency_type, latitude, longitude, status, read_status)
    VALUES (?, ?, ?, ?, 'ACTIVE', 'UNREAD')"
);

$stmt->bind_param(
    "ssdd",
    $barangay_id,
    $type,
    $latitude,
    $longitude
);

// Execute
if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Emergency received",
        "id" => $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Failed to save emergency: " . $stmt->error
    ]);
}

// Close
$stmt->close();
$conn->close();

?>