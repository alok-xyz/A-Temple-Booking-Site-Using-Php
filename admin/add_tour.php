<?php
require_once '../config/config.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: /ram/admin/login.php');
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
    
    $sql = "INSERT INTO darshan_tours (name, description, timetable, base_price, wheelchair_price, 
            assistant_price, food_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdddds", $name, $description, $timetable, $base_price, $wheelchair_price, 
                      $assistant_price, $food_price, $status);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Tour added successfully!";
        header('Location: /ram/admin/tours.php');
        exit();
    } else {
        $error = "Failed to add tour. Please try again.";
    }
}

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Add New Tour</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Tour Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Time Schedule</label>
                            <div id="timeSlots">
                                <div class="time-slot mb-2">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="time" class="form-control start-time" placeholder="Start Time">
                                        </div>
                                        <div class="col-md-3">
                                            <input type="time" class="form-control end-time" placeholder="End Time">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="text" class="form-control slot-description" placeholder="Description">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-danger btn-sm remove-slot">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm mt-2" id="addTimeSlot">
                                <i class="fas fa-plus"></i> Add Time Slot
                            </button>
                            <input type="hidden" name="timetable" id="timetableJSON">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Base Price (₹)</label>
                                <input type="number" class="form-control" name="base_price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Wheelchair Price (₹)</label>
                                <input type="number" class="form-control" name="wheelchair_price" step="0.01" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assistant Price (₹)</label>
                                <input type="number" class="form-control" name="assistant_price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Food Price (₹)</label>
                                <input type="number" class="form-control" name="food_price" step="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/ram/admin/tours.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Tour</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const timeSlots = document.getElementById('timeSlots');
    const addSlotBtn = document.getElementById('addTimeSlot');
    const timetableJSON = document.getElementById('timetableJSON');
    
    // Add new slot
    addSlotBtn.addEventListener('click', function() {
        const newSlot = document.querySelector('.time-slot').cloneNode(true);
        newSlot.querySelector('.start-time').value = '';
        newSlot.querySelector('.end-time').value = '';
        newSlot.querySelector('.slot-description').value = '';
        timeSlots.appendChild(newSlot);
        
        // Add remove button functionality
        newSlot.querySelector('.remove-slot').addEventListener('click', function() {
            newSlot.remove();
            updateTimetableJSON();
        });
    });
    
    // Form submission
    document.querySelector('form').addEventListener('submit', function(e) {
        updateTimetableJSON();
    });
    
    // Update JSON
    function updateTimetableJSON() {
        const slots = [];
        document.querySelectorAll('.time-slot').forEach(slot => {
            const startTime = slot.querySelector('.start-time').value;
            const endTime = slot.querySelector('.end-time').value;
            const description = slot.querySelector('.slot-description').value;
            
            if (startTime && endTime) {
                slots.push({
                    start_time: startTime,
                    end_time: endTime,
                    description: description
                });
            }
        });
        timetableJSON.value = JSON.stringify(slots);
    }
    
    // Initial setup
    document.querySelectorAll('.remove-slot').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.time-slot').remove();
            updateTimetableJSON();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?> 