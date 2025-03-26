<?php
require_once 'config/config.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

try {
    // Get tour details from database to ensure correct prices
    $tour_sql = "SELECT base_price, wheelchair_price, assistant_price, food_price 
                 FROM darshan_tours WHERE id = ?";
    $tour_stmt = $conn->prepare($tour_sql);
    $tour_stmt->bind_param("i", $_POST['tour_id']);
    $tour_stmt->execute();
    $tour_result = $tour_stmt->get_result();
    $tour_data = $tour_result->fetch_assoc();

    if (!$tour_data) {
        throw new Exception("Invalid tour selected");
    }

    // Log the incoming data for debugging
    error_log("Booking Data: " . print_r($_POST, true));

    // Add detailed logging
    error_log("Time Slot Data - slot: " . $_POST['time_slot'] . ", text: " . $_POST['time_slot_text']);

    // Store booking data in session with verified prices
    $_SESSION['booking_data'] = [
        'tour_id' => $_POST['tour_id'],
        'tour_name' => $_POST['tour_name'],
        'booking_date' => $_POST['booking_date'],
        'time_slot' => $_POST['time_slot'],         // Format: "07:10-09:00"
        'time_slot_text' => $_POST['time_slot_text'], // Format: "07:10 - 09:00"
        'total_people' => $_POST['total_people'],
        'base_price' => $tour_data['base_price'],
        'wheelchair_price' => $tour_data['wheelchair_price'],
        'assistant_price' => $tour_data['assistant_price'],
        'food_price' => $tour_data['food_price'],
        'total_amount' => $_POST['total_amount'],
        'visitors' => $_POST['visitors']
    ];
    
    echo json_encode(['status' => 'success']);
} catch(Exception $e) {
    error_log("Booking Session Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?> 