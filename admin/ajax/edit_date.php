<?php
require_once '../../config/config.php';

if(!isLoggedIn() || !isAdmin() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    exit('Unauthorized access');
}

$date_id = $_POST['id'];
$new_date = $_POST['date'];
$new_slots = $_POST['slots'];

// Check if new date already exists for this tour
$check_sql = "SELECT ad2.id FROM available_dates ad1 
              JOIN available_dates ad2 ON ad1.tour_id = ad2.tour_id AND ad2.date = ? 
              WHERE ad1.id = ? AND ad2.id != ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("sii", $new_date, $date_id, $date_id);
$check_stmt->execute();

if($check_stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    exit('Date already exists for this tour');
}

// Update date
$sql = "UPDATE available_dates SET date = ?, slots_available = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $new_date, $new_slots, $date_id);

if($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update date']);
} 