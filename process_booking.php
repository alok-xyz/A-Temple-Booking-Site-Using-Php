<?php
require_once 'config/config.php';
require_once 'vendor/autoload.php'; // For Razorpay

use Razorpay\Api\Api;

if(!isLoggedIn() || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: /ram/login.php');
    exit();
}

try {
    $conn->begin_transaction();

    $tour_id = $_POST['tour_id'];
    $booking_date = $_POST['booking_date'];
    $total_people = $_POST['total_people'];
    $visitors = $_POST['visitors'];
    
    // Calculate total amount
    $total_amount = 0;
    $tour_sql = "SELECT * FROM darshan_tours WHERE id = ?";
    $tour_stmt = $conn->prepare($tour_sql);
    $tour_stmt->bind_param("i", $tour_id);
    $tour_stmt->execute();
    $tour = $tour_stmt->get_result()->fetch_assoc();
    
    $total_amount = $tour['base_price'] * $total_people;
    
    foreach($visitors as $visitor) {
        if(isset($visitor['wheelchair'])) $total_amount += $tour['wheelchair_price'];
        if(isset($visitor['assistant'])) $total_amount += $tour['assistant_price'];
        if(isset($visitor['food'])) $total_amount += $tour['food_price'];
    }
    
    // Create booking
    $booking_sql = "INSERT INTO bookings (user_id, tour_id, booking_date, total_people, total_amount) 
                   VALUES (?, ?, ?, ?, ?)";
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param("iisid", $_SESSION['user_id'], $tour_id, $booking_date, $total_people, $total_amount);
    $booking_stmt->execute();
    $booking_id = $conn->insert_id;
    
    // Add visitors
    $visitor_sql = "INSERT INTO visitors (booking_id, full_name, age, gender, aadhar_number, 
                   has_disability, needs_wheelchair, needs_assistant, needs_food) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $visitor_stmt = $conn->prepare($visitor_sql);
    
    foreach($visitors as $visitor) {
        $has_disability = isset($visitor['disability']) ? 1 : 0;
        $needs_wheelchair = isset($visitor['wheelchair']) ? 1 : 0;
        $needs_assistant = isset($visitor['assistant']) ? 1 : 0;
        $needs_food = isset($visitor['food']) ? 1 : 0;
        
        $visitor_stmt->bind_param("isisiiiii", 
            $booking_id, 
            $visitor['name'], 
            $visitor['age'], 
            $visitor['gender'], 
            $visitor['aadhar'],
            $has_disability,
            $needs_wheelchair,
            $needs_assistant,
            $needs_food
        );
        $visitor_stmt->execute();
    }
    
    // Update available slots
    $slots_sql = "UPDATE available_dates SET slots_available = slots_available - ? 
                 WHERE tour_id = ? AND date = ? AND slots_available >= ?";
    $slots_stmt = $conn->prepare($slots_sql);
    $slots_stmt->bind_param("iisi", $total_people, $tour_id, $booking_date, $total_people);
    $slots_stmt->execute();
    
    if($slots_stmt->affected_rows == 0) {
        throw new Exception("No available slots for the selected date");
    }
    
    // Initialize Razorpay payment
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    
    $order = $api->order->create([
        'amount' => $total_amount * 100, // Amount in paise
        'currency' => 'INR',
        'receipt' => 'booking_' . $booking_id
    ]);
    
    $conn->commit();
    
    // Store order ID in session for verification
    $_SESSION['razorpay_order_id'] = $order['id'];
    $_SESSION['booking_id'] = $booking_id;
    
    // Redirect to payment page
    header('Location: /ram/payment.php');
    exit();
    
} catch(Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    header('Location: /ram/booking.php');
    exit();
} 