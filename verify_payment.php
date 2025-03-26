<?php
require_once 'config/config.php';
require 'vendor/autoload.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

if (!isLoggedIn() || !isset($_SESSION['booking_data'])) {
    header('Location: /ram/booking.php');
    exit();
}

$success = false;
$error = "Payment Failed";

if (!empty($_GET['razorpay_payment_id']) && !empty($_GET['razorpay_order_id']) && !empty($_GET['razorpay_signature'])) {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    try {
        $attributes = array(
            'razorpay_payment_id' => $_GET['razorpay_payment_id'],
            'razorpay_order_id' => $_GET['razorpay_order_id'],
            'razorpay_signature' => $_GET['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
        
        $conn->begin_transaction();

        // Get the booking data from session
        $booking_data = $_SESSION['booking_data'];
        
        // First check if slots are still available
        $check_slots_sql = "SELECT slots_available FROM available_dates 
                           WHERE tour_id = ? AND date = ? AND slots_available >= ?
                           FOR UPDATE"; // Add lock for concurrency
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
        
        // Insert booking record with time slot
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
        $payment_id = $_GET['razorpay_payment_id'];
        
        // Fix: Corrected the number of parameters in bind_param
        // i = integer, s = string, d = double
        $booking_stmt->bind_param("iisssids", // 8 parameters: 3 integers, 4 strings, 1 double
            $_SESSION['user_id'],      // i
            $booking_data['tour_id'],  // i
            $booking_data['booking_date'],    // s
            $booking_data['time_slot'],       // s
            $booking_data['time_slot_text'],  // s
            $booking_data['total_people'],    // i
            $booking_data['total_amount'],    // d
            $payment_id                       // s
        );
        
        $booking_stmt->execute();
        $booking_id = $conn->insert_id;
        
        // Insert visitors
        foreach ($booking_data['visitors'] as $visitor) {
            $sql = "INSERT INTO visitors (booking_id, full_name, age, gender, aadhar_number, 
                    has_disability, needs_wheelchair, needs_assistant, needs_food) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isisiiiii", 
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
            $stmt->execute();
        }

        // Update available slots
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
        
        $success = true;
        unset($_SESSION['booking_data']); // Clear booking data
        
        $conn->commit();
    } catch(SignatureVerificationError $e) {
        $conn->rollback();
        $error = 'Payment verification failed: ' . $e->getMessage();
    } catch(Exception $e) {
        $conn->rollback();
        $error = 'An error occurred: ' . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($success): ?>
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Payment Successful!</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                        </div>
                        <h5>Thank you for your booking!</h5>
                        <p>Your payment has been processed successfully. Your booking ID is: <strong><?php echo $booking_id; ?></strong></p>
                        <p>Payment ID: <?php echo htmlspecialchars($_GET['razorpay_payment_id']); ?></p>
                        <div class="mt-4">
                            <a href="/ram/download_receipt.php?id=<?php echo $booking_id; ?>" 
                               class="btn btn-primary">Download Receipt</a>
                            <a href="/ram/dashboard.php" class="btn btn-secondary">View My Bookings</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Payment Failed</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 48px;"></i>
                        </div>
                        <h5>Sorry, your payment could not be processed.</h5>
                        <p><?php echo htmlspecialchars($error); ?></p>
                        <div class="mt-4">
                            <a href="/ram/payment.php" class="btn btn-primary">Try Again</a>
                            <a href="/ram/booking.php" class="btn btn-secondary">Back to Booking</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 