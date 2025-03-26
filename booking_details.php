<?php
require_once 'config/config.php';

if(!isLoggedIn()) {
    header('Location: /ram/login.php');
    exit();
}

$booking_id = $_GET['id'] ?? 0;

// Fetch booking details
$sql = "SELECT b.*, dt.name as tour_name, dt.description,
        b.time_slot, b.time_slot_text 
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

// Fetch visitors
$visitors_sql = "SELECT * FROM visitors WHERE booking_id = ?";
$visitors_stmt = $conn->prepare($visitors_sql);
$visitors_stmt->bind_param("i", $booking_id);
$visitors_stmt->execute();
$visitors = $visitors_stmt->get_result();

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="card-title">Booking Details #<?php echo $booking['id']; ?></h3>
                        <span class="badge bg-<?php echo $booking['status'] == 'confirmed' ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>

                    <div class="booking-info mb-4">
                        <h5>Tour Information</h5>
                        <p><strong>Tour Name:</strong> <?php echo $booking['tour_name']; ?></p>
                        <p><strong>Description:</strong> <?php echo $booking['description']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['booking_date'])); ?></p>
                        <p><strong>Time Slot:</strong> <?php 
                            echo !empty($booking['time_slot_text']) 
                                ? $booking['time_slot_text'] 
                                : (!empty($booking['time_slot']) 
                                    ? str_replace('-', ' - ', $booking['time_slot']) 
                                    : 'Not specified'); 
                        ?></p>
                        <p><strong>Total Amount:</strong> ₹<?php echo $booking['total_amount']; ?></p>
                        <?php if($booking['payment_id']): ?>
                            <p><strong>Payment ID:</strong> <?php echo $booking['payment_id']; ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="visitors-info">
                        <h5>Visitor Details</h5>
                        <?php while($visitor = $visitors->fetch_assoc()): ?>
                            <div class="visitor-card mb-3 p-3 border rounded">
                                <h6>Visitor Information</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> <?php echo $visitor['full_name']; ?></p>
                                        <p><strong>Age:</strong> <?php echo $visitor['age']; ?></p>
                                        <p><strong>Gender:</strong> <?php echo ucfirst($visitor['gender']); ?></p>
                                        <p><strong>Aadhar:</strong> <?php echo $visitor['aadhar_number']; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if($visitor['has_disability']): ?>
                                            <p><strong>Disability:</strong> Yes</p>
                                            <?php if($visitor['needs_wheelchair']): ?>
                                                <p>- Needs Wheelchair</p>
                                            <?php endif; ?>
                                            <?php if($visitor['needs_assistant']): ?>
                                                <p>- Needs Assistant</p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if($visitor['needs_food']): ?>
                                            <p><strong>Food:</strong> Veg Thali</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="mt-4">
                        <a href="/ram/download_receipt.php?id=<?php echo $booking['id']; ?>" 
                           class="btn btn-primary">Download Receipt</a>
                        <a href="/ram/dashboard.php" class="btn btn-outline-secondary ms-2">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 