<?php
require_once '../../config/config.php';

if(!isLoggedIn() || !isAdmin() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    exit('Unauthorized access');
}

$tour_id = $_POST['tour_id'];
$date = $_POST['date'];
$slots = $_POST['slots'];

// Check if date already exists
$check_sql = "SELECT id FROM available_dates WHERE tour_id = ? AND date = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("is", $tour_id, $date);
$check_stmt->execute();

if($check_stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    exit('Date already exists for this tour');
}

// Add new date
$sql = "INSERT INTO available_dates (tour_id, date, slots_available) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $tour_id, $date, $slots);

if($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to add date']);
} 