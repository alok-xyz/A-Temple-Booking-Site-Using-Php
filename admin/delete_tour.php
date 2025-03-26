<?php
require_once '../config/config.php';

if(!isLoggedIn() || !isAdmin() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    exit('Unauthorized access');
}

$tour_id = $_POST['id'];

try {
    $conn->begin_transaction();

    // Check if there are any confirmed bookings
    $check_sql = "SELECT id FROM bookings WHERE tour_id = ? AND status = 'confirmed'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $tour_id);
    $check_stmt->execute();

    if($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Cannot delete tour with confirmed bookings');
    }

    // Delete pending bookings and their visitors
    $pending_bookings_sql = "SELECT id FROM bookings WHERE tour_id = ? AND status = 'pending'";
    $pending_stmt = $conn->prepare($pending_bookings_sql);
    $pending_stmt->bind_param("i", $tour_id);
    $pending_stmt->execute();
    $pending_result = $pending_stmt->get_result();

    while($booking = $pending_result->fetch_assoc()) {
        // Delete visitors
        $delete_visitors_sql = "DELETE FROM visitors WHERE booking_id = ?";
        $delete_visitors_stmt = $conn->prepare($delete_visitors_sql);
        $delete_visitors_stmt->bind_param("i", $booking['id']);
        $delete_visitors_stmt->execute();

        // Delete booking
        $delete_booking_sql = "DELETE FROM bookings WHERE id = ?";
        $delete_booking_stmt = $conn->prepare($delete_booking_sql);
        $delete_booking_stmt->bind_param("i", $booking['id']);
        $delete_booking_stmt->execute();
    }

    // Delete available dates
    $delete_dates_sql = "DELETE FROM available_dates WHERE tour_id = ?";
    $delete_dates_stmt = $conn->prepare($delete_dates_sql);
    $delete_dates_stmt->bind_param("i", $tour_id);
    $delete_dates_stmt->execute();

    // Delete tour
    $delete_tour_sql = "DELETE FROM darshan_tours WHERE id = ?";
    $delete_tour_stmt = $conn->prepare($delete_tour_sql);
    $delete_tour_stmt->bind_param("i", $tour_id);
    $delete_tour_stmt->execute();

    $conn->commit();
    echo json_encode(['status' => 'success']);

} catch(Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 