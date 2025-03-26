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

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $timetable = $_POST['timetable'];
    $base_price = $_POST['base_price'];
    $wheelchair_price = $_POST['wheelchair_price'];
    $assistant_price = $_POST['assistant_price'];
    $food_price = $_POST['food_price'];
    $status = $_POST['status'];
    
    $sql = "UPDATE darshan_tours SET 
            name = ?, 
            description = ?, 
            timetable = ?,
            base_price = ?, 
            wheelchair_price = ?, 
            assistant_price = ?, 
            food_price = ?, 
            status = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("sssddddsi", 
        $name,
        $description,
        $timetable,
        $base_price,
        $wheelchair_price,
        $assistant_price,
        $food_price,
        $status,
        $tour_id
    );
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Tour updated successfully!";
        header('Location: /ram/admin/tours.php');
        exit();
    } else {
        $error = "Failed to update tour. Please try again.";
    }
}

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Edit Tour</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" id="editTourForm">
                        <div class="mb-3">
                            <label class="form-label">Tour Name</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo htmlspecialchars($tour['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Time Schedule</label>
                            <div class="card">
                                <div class="card-body">
                                    <div class="timetable-slots">
                                        <?php 
                                        $timetable = json_decode($tour['timetable'] ?? '[]', true);
                                        if (empty($timetable)) {
                                            // Default empty slot
                                            ?>
                                            <div class="row mb-3 slot-row">
                                                <div class="col-md-3">
                                                    <label class="form-label">Start Time</label>
                                                    <input type="time" class="form-control start-time" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">End Time</label>
                                                    <input type="time" class="form-control end-time" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">Description</label>
                                                    <input type="text" class="form-control slot-description" 
                                                           placeholder="e.g., Morning Darshan" required>
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger remove-slot" 
                                                            style="display: none;">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <?php
                                        } else {
                                            foreach ($timetable as $slot) {
                                                ?>
                                                <div class="row mb-3 slot-row">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Start Time</label>
                                                        <input type="time" class="form-control start-time" 
                                                               value="<?php echo htmlspecialchars($slot['start_time']); ?>" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">End Time</label>
                                                        <input type="time" class="form-control end-time" 
                                                               value="<?php echo htmlspecialchars($slot['end_time']); ?>" required>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="form-label">Description</label>
                                                        <input type="text" class="form-control slot-description" 
                                                               value="<?php echo htmlspecialchars($slot['description']); ?>" 
                                                               placeholder="e.g., Morning Darshan" required>
                                                    </div>
                                                    <div class="col-md-1 d-flex align-items-end">
                                                        <button type="button" class="btn btn-danger remove-slot">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm" id="addSlot">
                                        <i class="fas fa-plus"></i> Add Time Slot
                                    </button>
                                    <input type="hidden" name="timetable" id="timetableJSON">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required>
                                <?php echo $tour['description']; ?>
                            </textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Base Price (₹)</label>
                                <input type="number" class="form-control" name="base_price" 
                                       value="<?php echo $tour['base_price']; ?>" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Wheelchair Price (₹)</label>
                                <input type="number" class="form-control" name="wheelchair_price" 
                                       value="<?php echo $tour['wheelchair_price']; ?>" step="0.01" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assistant Price (₹)</label>
                                <input type="number" class="form-control" name="assistant_price" 
                                       value="<?php echo $tour['assistant_price']; ?>" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Food Price (₹)</label>
                                <input type="number" class="form-control" name="food_price" 
                                       value="<?php echo $tour['food_price']; ?>" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="active" <?php echo $tour['status'] == 'active' ? 'selected' : ''; ?>>
                                    Active
                                </option>
                                <option value="inactive" <?php echo $tour['status'] == 'inactive' ? 'selected' : ''; ?>>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/ram/admin/tours.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Tour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timetableSlots = document.querySelector('.timetable-slots');
    const addSlotBtn = document.getElementById('addSlot');
    const timetableJSON = document.getElementById('timetableJSON');
    
    // Initialize timetableJSON with existing data
    updateTimetableJSON();
    
    // Add new slot
    addSlotBtn.addEventListener('click', function() {
        const newSlot = document.querySelector('.slot-row').cloneNode(true);
        newSlot.querySelector('.start-time').value = '';
        newSlot.querySelector('.end-time').value = '';
        newSlot.querySelector('.slot-description').value = '';
        newSlot.querySelector('.remove-slot').style.display = 'block';
        timetableSlots.appendChild(newSlot);
        
        // Add event listener to remove button
        newSlot.querySelector('.remove-slot').addEventListener('click', function() {
            newSlot.remove();
            updateTimetableJSON();
        });
    });
    
    // Add event listeners to existing remove buttons
    document.querySelectorAll('.remove-slot').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.slot-row').remove();
            updateTimetableJSON();
        });
    });
    
    // Update JSON when form is submitted
    document.getElementById('editTourForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateTimetableJSON();
        this.submit();
    });
    
    // Function to update timetable JSON
    function updateTimetableJSON() {
        const slots = [];
        document.querySelectorAll('.slot-row').forEach(row => {
            slots.push({
                start_time: row.querySelector('.start-time').value,
                end_time: row.querySelector('.end-time').value,
                description: row.querySelector('.slot-description').value
            });
        });
        timetableJSON.value = JSON.stringify(slots);
    }
});
</script>

<?php include 'includes/footer.php'; ?> 