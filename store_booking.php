<?php
require_once 'config/config.php';

if(!isLoggedIn() || !isset($_SESSION['booking_data'])) {
    header('Location: /ram/booking.php');
    exit();
}

$booking_data = $_SESSION['booking_data'];

try {
    $conn->begin_transaction();

    // First check if slots are still available
    $check_slots_sql = "SELECT slots_available FROM available_dates 
                       WHERE tour_id = ? AND date = ? AND slots_available >= ?";
    $check_stmt = $conn->prepare($check_slots_sql);
    $check_stmt->bind_param("isi", 
        $booking_data['tour_id'],
        $booking_data['booking_date'],
        $booking_data['total_people']
    );
    $check_stmt->execute();
    $slots_result = $check_stmt->get_result();

    if (!$slots_result->fetch_assoc()) {
        throw new Exception("Sorry, not enough slots available for this date.");
    }

    // Insert booking record
    $booking_sql = "INSERT INTO bookings (
        user_id, 
        tour_id, 
        booking_date, 
        time_slot,
        time_slot_text,
        total_people, 
        total_amount, 
        status,
        payment_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)";

    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param("iisssisd", 
        $_SESSION['user_id'],
        $booking_data['tour_id'],
        $booking_data['booking_date'],
        $booking_data['time_slot'],
        $booking_data['time_slot_text'],
        $booking_data['total_people'],
        $booking_data['total_amount'],
        $payment_id
    );
    $booking_stmt->execute();
    $booking_id = $conn->insert_id;

    // Insert visitor details
    $visitor_sql = "INSERT INTO visitors (booking_id, full_name, age, gender, aadhar_number, 
                   has_disability, needs_wheelchair, needs_assistant, needs_food) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $visitor_stmt = $conn->prepare($visitor_sql);

    foreach($booking_data['visitors'] as $visitor) {
        $visitor_stmt->bind_param("isisiiiii", 
            $booking_id,
            $visitor['name'],
            $visitor['age'],
            $visitor['gender'],
            $visitor['aadhar'],
            $visitor['disability'],
            $visitor['wheelchair'],
            $visitor['assistant'],
            $visitor['food']
        );
        $visitor_stmt->execute();
    }

    // Update available slots with locking to prevent race conditions
    $update_slots = "UPDATE available_dates 
                    SET slots_available = slots_available - ? 
                    WHERE tour_id = ? AND date = ? 
                    AND slots_available >= ?";
    $update_stmt = $conn->prepare($update_slots);
    $update_stmt->bind_param("iisi", 
        $booking_data['total_people'],
        $booking_data['tour_id'],
        $booking_data['booking_date'],
        $booking_data['total_people']
    );
    $result = $update_stmt->execute();

    if ($update_stmt->affected_rows === 0) {
        throw new Exception("Failed to update available slots. Please try again.");
    }

    $conn->commit();
    unset($_SESSION['booking_data']); // Clear booking data from session

    header('Location: /ram/booking_success.php?id=' . $booking_id);
} catch(Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header('Location: /ram/booking.php');
}
?> 