<?php
require_once '../config/config.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: /ram/admin/login.php');
    exit();
}

$tour_id = $_GET['id'] ?? 0;

// Fetch tour details
$sql = "SELECT * FROM darshan_tours WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tour_id);
$stmt->execute();
$tour = $stmt->get_result()->fetch_assoc();

if(!$tour) {
    header('Location: /ram/admin/tours.php');
    exit();
}

// Add new date if form submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $slots = $_POST['slots'];
    
    // Check if date already exists
    $check_sql = "SELECT id FROM available_dates WHERE tour_id = ? AND date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $tour_id, $date);
    $check_stmt->execute();
    
    if($check_stmt->get_result()->num_rows > 0) {
        $error = "This date already exists for this tour.";
    } else {
        $insert_sql = "INSERT INTO available_dates (tour_id, date, slots_available) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isi", $tour_id, $date, $slots);
        
        if($insert_stmt->execute()) {
            $success = "Date added successfully!";
        } else {
            $error = "Failed to add date. Please try again.";
        }
    }
}

// Fetch available dates
$dates_sql = "SELECT * FROM available_dates WHERE tour_id = ? ORDER BY date ASC";
$dates_stmt = $conn->prepare($dates_sql);
$dates_stmt->bind_param("i", $tour_id);
$dates_stmt->execute();
$dates = $dates_stmt->get_result();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h2>Manage Dates - <?php echo $tour['name']; ?></h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDateModal">
                <i class="fas fa-plus"></i> Add New Date
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Slots Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($date = $dates->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($date['date'])); ?></td>
                                <td><?php echo $date['slots_available']; ?></td>
                                <td class="table-actions">
                                    <button class="btn btn-sm btn-info edit-date" 
                                            data-id="<?php echo $date['id']; ?>"
                                            data-date="<?php echo $date['date']; ?>"
                                            data-slots="<?php echo $date['slots_available']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-date" 
                                            data-id="<?php echo $date['id']; ?>">
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

<!-- Add Date Modal -->
<div class="modal fade" id="addDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDateForm">
                    <input type="hidden" name="tour_id" value="<?php echo $tour_id; ?>">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Slots</label>
                        <input type="number" class="form-control" name="slots" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveDate">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Date Modal -->
<div class="modal fade" id="editDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editDateForm">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" id="edit_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Available Slots</label>
                        <input type="number" class="form-control" name="slots" id="edit_slots" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="updateDate">Update</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add new date
    $('#saveDate').click(function() {
        $.ajax({
            url: '/ram/admin/ajax/add_date.php',
            method: 'POST',
            data: $('#addDateForm').serialize(),
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Failed to add date. Please try again.');
            }
        });
    });

    // Delete date
    $('.delete-date').click(function() {
        if(confirm('Are you sure you want to delete this date?')) {
            const dateId = $(this).data('id');
            $.ajax({
                url: '/ram/admin/ajax/delete_date.php',
                method: 'POST',
                data: { id: dateId },
                success: function(response) {
                    location.reload();
                },
                error: function() {
                    alert('Failed to delete date. Please try again.');
                }
            });
        }
    });

    // Edit date
    $('.edit-date').click(function() {
        const id = $(this).data('id');
        const date = $(this).data('date');
        const slots = $(this).data('slots');
        
        $('#edit_id').val(id);
        $('#edit_date').val(date);
        $('#edit_slots').val(slots);
        
        $('#editDateModal').modal('show');
    });

    // Update date
    $('#updateDate').click(function() {
        $.ajax({
            url: '/ram/admin/ajax/update_date.php',
            method: 'POST',
            data: $('#editDateForm').serialize(),
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Failed to update date. Please try again.');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 