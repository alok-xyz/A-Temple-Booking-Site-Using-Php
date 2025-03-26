<?php
require_once '../../config/config.php';

if(!isLoggedIn() || !isAdmin() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    exit('Unauthorized access');
}

$booking_id = $_POST['id'];
$status = $_POST['status'];

try {
    $conn->begin_transaction();

    // Update booking status
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);
    $stmt->execute();

    // If cancelling, update available slots
    if($status == 'cancelled') {
        $booking_sql = "SELECT total_people, tour_id, booking_date FROM bookings WHERE id = ?";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("i", $booking_id);
        $booking_stmt->execute();
        $booking = $booking_stmt->get_result()->fetch_assoc();

        $update_slots_sql = "UPDATE available_dates 
                            SET slots_available = slots_available + ? 
                            WHERE tour_id = ? AND date = ?";
        $update_slots_stmt = $conn->prepare($update_slots_sql);
        $update_slots_stmt->bind_param("iis", 
            $booking['total_people'], 
            $booking['tour_id'], 
            $booking['booking_date']
        );
        $update_slots_stmt->execute();
    }

    $conn->commit();
    echo json_encode(['status' => 'success']);

} catch(Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 