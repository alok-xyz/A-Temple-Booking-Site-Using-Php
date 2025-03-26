<?php
require_once '../config/config.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: /ram/admin/login.php');
    exit();
}

// Get filter parameters
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$sql = "SELECT b.*, u.name as user_name, u.email as user_email, dt.name as tour_name 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN darshan_tours dt ON b.tour_id = dt.id 
        WHERE 1=1";
$params = [];
$types = "";

if($status) {
    $sql .= " AND b.status = ?";
    $params[] = $status;
    $types .= "s";
}
if($date_from) {
    $sql .= " AND b.booking_date >= ?";
    $params[] = $date_from;
    $types .= "s";
}
if($date_to) {
    $sql .= " AND b.booking_date <= ?";
    $params[] = $date_to;
    $types .= "s";
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$bookings = $stmt->get_result();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Manage Bookings</h2>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status">
                        <option value="">All</option>
                        <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="/ram/admin/bookings.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>User</th>
                            <th>Tour</th>
                            <th>Date</th>
                            <th>People</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $booking['id']; ?></td>
                                <td>
                                    <?php echo $booking['user_name']; ?><br>
                                    <small class="text-muted"><?php echo $booking['user_email']; ?></small>
                                </td>
                                <td><?php echo $booking['tour_name']; ?></td>
                                <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                                <td><?php echo $booking['total_people']; ?></td>
                                <td>₹<?php echo $booking['total_amount']; ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $booking['status'] == 'confirmed' ? 'success' : 
                                            ($booking['status'] == 'cancelled' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($booking['created_at'])); ?></td>
                                <td class="table-actions">
                                    <a href="/ram/admin/booking_details.php?id=<?php echo $booking['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if($booking['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-success confirm-booking" 
                                                data-id="<?php echo $booking['id']; ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger cancel-booking" 
                                                data-id="<?php echo $booking['id']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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