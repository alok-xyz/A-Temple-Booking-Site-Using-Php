<?php
require_once 'config/config.php';

if(!isLoggedIn()) {
    header('Location: /ram/login.php');
    exit();
}

// Fetch user's bookings
$sql = "SELECT b.*, dt.name as tour_name 
        FROM bookings b 
        JOIN darshan_tours dt ON b.tour_id = dt.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bookings = $stmt->get_result();

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
/* Update the default link style */
.nav-link.btn {
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
    background-color: #f8f9fa !important; /* Light background */
    color: #212529 !important; /* Dark text */
    margin-bottom: 5px;
    font-weight: 500; /* Make text slightly bolder */
    box-shadow: 0 1px 3px rgba(0,0,0,0.1); /* Subtle shadow */
}

/* Hover effect */
.nav-link.btn:hover {
    transform: translateX(5px);
    background-color: #dc3545 !important;
    color: white !important;
    border-color: #dc3545;
}

/* Active state */
.nav-link.btn:active {
    background-color: #c82333 !important;
    color: white !important;
    border-color: #bd2130;
}

/* Remove the hover-danger class as we've incorporated its styles above */

.page-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.content-wrapper {
    flex: 1 0 auto;
}

footer {
    flex-shrink: 0;
    width: 100%;
    background-color: #333;
    color: white;
    padding: 20px 0;
    margin-top: auto;
}
</style>

<div class="page-wrapper">
    <div class="content-wrapper">
        <div class="container my-5">
            <div class="row mb-4">
                <div class="col">
                    <h2 class="text-danger mb-0"><i class="fas fa-tachometer-alt"></i> My Dashboard</h2>
                </div>
            </div>
            
            <div class="row">
                <!-- Quick Links Card -->
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-link"></i> Quick Links</h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav flex-column">
                                <li class="nav-item mb-2">
                                    <a class="nav-link btn" href="/ram/booking.php">
                                        <i class="fas fa-ticket-alt me-2"></i> New Booking
                                    </a>
                                </li>
                                <li class="nav-item mb-2">
                                    <a class="nav-link btn" href="/ram/profile.php">
                                        <i class="fas fa-user me-2"></i> My Profile
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn" href="/ram/change_password.php">
                                        <i class="fas fa-key me-2"></i> Change Password
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Bookings Card -->
                <div class="col-md-9">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="fas fa-list"></i> My Bookings</h5>
                            <a href="/ram/booking.php" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> New Booking
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if($bookings->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Tour</th>
                                                <th>Date</th>
                                                <th>People</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($booking = $bookings->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold">#<?php echo $booking['id']; ?></span>
                                                    </td>
                                                    <td><?php echo $booking['tour_name']; ?></td>
                                                    <td>
                                                        <i class="far fa-calendar-alt text-muted"></i>
                                                        <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                                                    </td>
                                                    <td>
                                                        <i class="fas fa-users text-muted"></i>
                                                        <?php echo $booking['total_people']; ?>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold">Rs. <?php echo number_format($booking['total_amount'], 2); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                        $statusClass = $booking['status'] == 'confirmed' ? 'success' : 'warning';
                                                        $statusIcon = $booking['status'] == 'confirmed' ? 'check-circle' : 'clock';
                                                        ?>
                                                        <span class="badge bg-<?php echo $statusClass; ?> text-white">
                                                            <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                                            <?php echo ucfirst($booking['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="/ram/booking_details.php?id=<?php echo $booking['id']; ?>" 
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                            <a href="/ram/download_receipt.php?id=<?php echo $booking['id']; ?>" 
                                                               class="btn btn-sm btn-success">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center my-5">
                                    <i class="fas fa-ticket-alt text-muted mb-3" style="font-size: 48px;"></i>
                                    <p class="lead">No bookings found.</p>
                                    <a href="/ram/booking.php" class="btn btn-danger">
                                        <i class="fas fa-plus me-2"></i>Book your first darshan
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</div> 