    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>Shree Ram Janmabhoomi</h5>
                    <p>Experience the divine darshan at the sacred temple in Ayodhya.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>/about.php" class="text-light">About Us</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/booking.php" class="text-light">Book Darshan</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/contact.php" class="text-light">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone"></i> +91 XXXXXXXXXX</li>
                        <li><i class="fas fa-envelope"></i> info@ramjanmabhoomi.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> Ayodhya, Uttar Pradesh</li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Developer</h5>
                    <div class="developer-info">
                        <p class="mb-2">Website developed by</p>
                        <a href="https://github.com/alok-xyz" target="_blank" class="dev-link">
                            <i class="fab fa-github me-2"></i>
                            <strong>Alok Guha Roy</strong>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Shree Ram Janmabhoomi. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Add logout script -->
    <?php if(isLoggedIn()): ?>
    <script>
    $(document).ready(function() {
        $('#logoutBtn').on('click', function(e) {
            e.preventDefault();
            $('#loadingOverlay').show();
            setTimeout(function() {
                window.location.href = '/ram/logout.php';
            }, 1000);
        });
    });
    </script>
    <?php endif; ?>
</body>
</html> 