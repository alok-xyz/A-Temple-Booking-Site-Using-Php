<?php
require_once 'config/config.php';

if(!isLoggedIn()) {
    header('Location: /ram/login.php');
    exit();
}

$booking_id = $_GET['id'] ?? 0;

// Fetch booking details
$sql = "SELECT b.*, dt.name as tour_name 
        FROM bookings b 
        JOIN darshan_tours dt ON b.tour_id = dt.id 
        WHERE b.id = ? AND b.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if(!$booking) {
    header('Location: /ram/dashboard.php');
    exit();
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                    <h3 class="mt-3">Booking Confirmed!</h3>
                    <p class="mb-4">Your booking has been confirmed successfully.</p>
                    
                    <div class="booking-details text-start mb-4">
                        <h5>Booking Details:</h5>
                        <p><strong>Booking ID:</strong> #<?php echo $booking['id']; ?></p>
                        <p><strong>Tour:</strong> <?php echo $booking['tour_name']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['booking_date'])); ?></p>
                        <p><strong>Total Amount:</strong> ₹<?php echo $booking['total_amount']; ?></p>
                        <p><strong>Payment ID:</strong> <?php echo $booking['payment_id']; ?></p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="/ram/download_receipt.php?id=<?php echo $booking['id']; ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-download"></i> Download Receipt
                        </a>
                        <a href="/ram/dashboard.php" class="btn btn-secondary">
                            Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 