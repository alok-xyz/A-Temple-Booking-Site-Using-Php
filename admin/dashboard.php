<?php
require_once '../config/config.php';

// Check if user is logged in and is admin
if(!isLoggedIn() || !isAdmin()) {
    header('Location: /ram/admin/login.php');
    exit();
}

// Get statistics
$stats = [
    'total_bookings' => $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'],
    'today_bookings' => $conn->query("SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'],
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'],
    'total_revenue' => $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['total'] ?? 0
];

// Get recent bookings
$recent_bookings = $conn->query("
    SELECT b.*, u.name as user_name, dt.name as tour_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN darshan_tours dt ON b.tour_id = dt.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
/* Dashboard Stats Cards */
.admin-stats {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    transition: transform 0.2s;
    border-left: 4px solid #dc3545;
}

.admin-stats:hover {
    transform: translateY(-5px);
}

.admin-stats h3 {
    color: #dc3545;
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 5px;
}

.admin-stats p {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 0;
    font-weight: 500;
}

/* Table Styling */
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 10px;
}

.card-header {
    background-color: #dc3545;
    color: white;
    border-radius: 10px 10px 0 0 !important;
    padding: 15px 20px;
}

.card-header h5 {
    font-weight: 600;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background-color: #f8f9fa;
    color: #495057;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    padding: 8px 12px;
    font-weight: 500;
}

.btn-primary {
    background-color: #0d6efd;
    border: none;
    padding: 5px 15px;
}

.btn-primary:hover {
    background-color: #0b5ed7;
}

/* Container Padding */
.container-fluid {
    padding: 30px;
}
</style>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-danger mb-4">
                <i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="admin-stats">
                <i class="fas fa-ticket-alt mb-3 text-danger" style="font-size: 24px;"></i>
                <h3><?php echo $stats['total_bookings']; ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="admin-stats">
                <i class="fas fa-calendar-check mb-3 text-danger" style="font-size: 24px;"></i>
                <h3><?php echo $stats['today_bookings']; ?></h3>
                <p>Today's Bookings</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="admin-stats">
                <i class="fas fa-users mb-3 text-danger" style="font-size: 24px;"></i>
                <h3><?php echo $stats['total_users']; ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="admin-stats">
                <i class="fas fa-rupee-sign mb-3 text-danger" style="font-size: 24px;"></i>
                <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Recent Bookings
                    </h5>
                    <a href="/ram/admin/bookings.php" class="btn btn-light btn-sm">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>User</th>
                                    <th>Tour</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold">#<?php echo $booking['id']; ?></td>
                                        <td>
                                            <i class="fas fa-user me-2 text-muted"></i>
                                            <?php echo $booking['user_name']; ?>
                                        </td>
                                        <td><?php echo $booking['tour_name']; ?></td>
                                        <td>
                                            <i class="far fa-calendar-alt me-2 text-muted"></i>
                                            <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                                        </td>
                                        <td class="fw-bold">₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = $booking['status'] == 'confirmed' ? 'success' : 'warning';
                                            $statusIcon = $booking['status'] == 'confirmed' ? 'check-circle' : 'clock';
                                            ?>
                                            <span class="badge bg-<?php echo $statusClass; ?>">
                                                <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="/ram/admin/booking_details.php?id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 