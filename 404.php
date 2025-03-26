<?php
require_once 'config/config.php';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="error-page py-5">
                <h1 class="display-1 text-danger mb-4">404</h1>
                <img src="/ram/assets/404.jpg" alt="404 Error" class="img-fluid mb-4" style="max-width: 300px;">
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead text-muted mb-4">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                    <a href="/ram/index.php" class="btn btn-primary btn-lg px-4">
                        <i class="fas fa-home me-2"></i>Go to Home
                    </a>
                    <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg px-4">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-page {
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.display-1 {
    font-size: 8rem;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.btn-lg {
    padding: 12px 24px;
}
</style>

<?php include 'includes/footer.php'; ?> 