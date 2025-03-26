<?php
require_once '../../config/config.php';

if(!isLoggedIn() || !isAdmin() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    exit('Unauthorized access');
}

$date_id = $_POST['id'];

// Check if there are any bookings for this date
$check_sql = "SELECT b.id FROM bookings b 
              JOIN available_dates ad ON b.tour_id = ad.tour_id AND b.booking_date = ad.date 
              WHERE ad.id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $date_id);
$check_stmt->execute();

if($check_stmt->get_result()->num_rows > 0) {
    http_response_code(400);
    exit('Cannot delete date with existing bookings');
}

// Delete date
$sql = "DELETE FROM available_dates WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $date_id);

if($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete date']);
} 