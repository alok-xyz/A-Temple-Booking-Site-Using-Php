<?php
require_once 'config/config.php';
include 'includes/header.php';
?>

<div class="hero-section">
    <div class="hero-background"></div>
    <div class="container">
        <h1 class="display-3 mb-4">Welcome to Shree Ram Janmabhoomi</h1>
        <p class="lead mb-4">Experience the divine darshan of Lord Ram at the sacred temple in Ayodhya</p>
        <a href="booking.php" class="btn btn-primary btn-lg">Book Darshan Now</a>
    </div>
</div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card h-100 text-center p-4">
                <div class="icon-wrapper mb-3">
                    <i class="fas fa-om fa-3x" style="color: #ff7f27;"></i>
                </div>
                <h3>Temple Darshan</h3>
                <p class="text-muted">Book your divine darshan at the sacred Ram Mandir in Ayodhya.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-hands-praying fa-3x mb-3" style="color: #ff7f27;"></i>
                    <h3 class="card-title">Special Puja</h3>
                    <p class="card-text">Participate in special pujas and ceremonies at the temple.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-hotel fa-3x mb-3" style="color: #ff7f27;"></i>
                    <h3 class="card-title">Accommodation</h3>
                    <p class="card-text">Find comfortable stays near the temple complex.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Put your JavaScript code here
    // This ensures the DOM is fully loaded before running any JavaScript
});
</script>

<?php include 'includes/footer.php'; ?> 