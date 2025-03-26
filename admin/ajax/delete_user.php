<?php
require_once '../../config/config.php';

if(!isLoggedIn() || !isAdmin() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(403);
    exit('Unauthorized access');
}

$user_id = $_POST['id'];

// Check if trying to delete self
if($user_id == $_SESSION['user_id']) {
    http_response_code(400);
    exit('Cannot delete your own account');
}

try {
    $conn->begin_transaction();

    // Check if user has any confirmed bookings
    $check_sql = "SELECT id FROM bookings WHERE user_id = ? AND status = 'confirmed'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();

    if($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('Cannot delete user with confirmed bookings');
    }

    // Delete user's visitors
    $visitors_sql = "DELETE v FROM visitors v 
                    JOIN bookings b ON v.booking_id = b.id 
                    WHERE b.user_id = ?";
    $visitors_stmt = $conn->prepare($visitors_sql);
    $visitors_stmt->bind_param("i", $user_id);
    $visitors_stmt->execute();

    // Delete user's bookings
    $bookings_sql = "DELETE FROM bookings WHERE user_id = ?";
    $bookings_stmt = $conn->prepare($bookings_sql);
    $bookings_stmt->bind_param("i", $user_id);
    $bookings_stmt->execute();

    // Delete user
    $user_sql = "DELETE FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();

    $conn->commit();
    echo json_encode(['status' => 'success']);

} catch(Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} 