<?php
require_once '../config/config.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: /ram/admin/login.php');
    exit();
}

$booking_id = $_GET['id'] ?? 0;

// Fetch booking details
$sql = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone,
        dt.name as tour_name, dt.description as tour_description,
        b.time_slot, b.time_slot_text
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN darshan_tours dt ON b.tour_id = dt.id 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

if(!$booking) {
    header('Location: /ram/admin/bookings.php');
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

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2 class="text-dark">Booking Details #<?php echo $booking_id; ?></h2>
            <a href="/ram/admin/bookings.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Booking Information -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-dark">
                    <h5 class="mb-0 text-white">Booking Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6>Status</h6>
                        <span class="badge bg-<?php 
                            echo $booking['status'] == 'confirmed' ? 'success' : 
                                ($booking['status'] == 'cancelled' ? 'danger' : 'warning'); 
                        ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>

                    <div class="mb-4">
                        <h6>Tour Details</h6>
                        <p><strong>Name:</strong> <?php echo $booking['tour_name']; ?></p>
                        <p><strong>Description:</strong> <?php echo $booking['tour_description']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['booking_date'])); ?></p>
                        <p><strong>Time Slot:</strong> <?php 
                            echo !empty($booking['time_slot_text']) 
                                ? $booking['time_slot_text'] 
                                : (!empty($booking['time_slot']) 
                                    ? str_replace('-', ' - ', $booking['time_slot']) 
                                    : 'Not specified'); 
                        ?></p>
                        <p><strong>Total People:</strong> <?php echo $booking['total_people']; ?></p>
                        <p><strong>Amount:</strong> ₹<?php echo $booking['total_amount']; ?></p>
                    </div>

                    <div class="mb-4">
                        <h6>User Information</h6>
                        <p><strong>Name:</strong> <?php echo $booking['user_name']; ?></p>
                        <p><strong>Email:</strong> <?php echo $booking['user_email']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $booking['user_phone']; ?></p>
                    </div>

                    <?php if($booking['payment_id']): ?>
                        <div class="mb-4">
                            <h6>Payment Information</h6>
                            <p><strong>Payment ID:</strong> <?php echo $booking['payment_id']; ?></p>
                            <p><strong>Payment Date:</strong> 
                                <?php echo date('d M Y H:i', strtotime($booking['created_at'])); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Visitors Information -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-dark">
                    <h5 class="mb-0 text-white">Visitor Details</h5>
                </div>
                <div class="card-body">
                    <?php while($visitor = $visitors->fetch_assoc()): ?>
                        <div class="visitor-card mb-4 p-3 border rounded">
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
            </div>
        </div>
    </div>

    <?php if($booking['status'] == 'pending'): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Actions</h5>
                        <button class="btn btn-success me-2 confirm-booking" data-id="<?php echo $booking_id; ?>">
                            <i class="fas fa-check"></i> Confirm Booking
                        </button>
                        <button class="btn btn-danger cancel-booking" data-id="<?php echo $booking_id; ?>">
                            <i class="fas fa-times"></i> Cancel Booking
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Confirm booking
    $('.confirm-booking').click(function() {
        if(confirm('Are you sure you want to confirm this booking?')) {
            const bookingId = $(this).data('id');
            $.ajax({
                url: '/ram/admin/ajax/update_booking_status.php',
                method: 'POST',
                data: { 
                    id: bookingId,
                    status: 'confirmed'
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Failed to update booking. Please try again.');
                }
            });
        }
    });

    // Cancel booking
    $('.cancel-booking').click(function() {
        if(confirm('Are you sure you want to cancel this booking?')) {
            const bookingId = $(this).data('id');
            $.ajax({
                url: '/ram/admin/ajax/update_booking_status.php',
                method: 'POST',
                data: { 
                    id: bookingId,
                    status: 'cancelled'
                },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Failed to update booking. Please try again.');
                }
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 