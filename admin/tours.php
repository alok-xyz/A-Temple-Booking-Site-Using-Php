<?php
require_once '../config/config.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: /ram/admin/login.php');
    exit();
}

// Get all tours
$tours = $conn->query("SELECT * FROM darshan_tours ORDER BY id DESC");

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Manage Tours</h2>
            <a href="/ram/admin/add_tour.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Tour
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Base Price</th>
                            <th>Wheelchair Price</th>
                            <th>Assistant Price</th>
                            <th>Food Price</th>
                            <th>Time Schedule</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($tour = $tours->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $tour['id']; ?></td>
                                <td><?php echo $tour['name']; ?></td>
                                <td>₹<?php echo $tour['base_price']; ?></td>
                                <td>₹<?php echo $tour['wheelchair_price']; ?></td>
                                <td>₹<?php echo $tour['assistant_price']; ?></td>
                                <td>₹<?php echo $tour['food_price']; ?></td>
                                <td>
                                    <?php 
                                    $timetable = json_decode($tour['timetable'], true);
                                    if ($timetable && is_array($timetable)) {
                                        foreach ($timetable as $slot) {
                                            echo '<div class="mb-1">';
                                            echo '<small>';
                                            echo htmlspecialchars($slot['start_time']) . ' - ' . htmlspecialchars($slot['end_time']);
                                            if (!empty($slot['description'])) {
                                                echo ': ' . htmlspecialchars($slot['description']);
                                            }
                                            echo '</small>';
                                            echo '</div>';
                                        }
                                    } else {
                                        echo '<small class="text-muted">No schedule set</small>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $tour['status'] == 'active' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($tour['status']); ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="/ram/admin/edit_tour.php?id=<?php echo $tour['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/ram/admin/manage_dates.php?id=<?php echo $tour['id']; ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-calendar"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-tour" 
                                            data-id="<?php echo $tour['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
    $('.delete-tour').click(function() {
        if(confirm('Are you sure you want to delete this tour?')) {
            const tourId = $(this).data('id');
            $.ajax({
                url: '/ram/admin/delete_tour.php',
                method: 'POST',
                data: { id: tourId },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Failed to delete tour. Please try again.');
                }
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 