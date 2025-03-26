<?php
require_once 'config/config.php';

if(isLoggedIn()) {
    header('Location: /ram/dashboard.php');
    exit();
}

$token = $_GET['token'] ?? '';
$valid_token = false;
$email = '';

if($token) {
    // Verify token
    $sql = "SELECT email FROM password_resets 
            WHERE token = ? AND used = 0 
            AND expires_at > NOW() 
            ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $valid_token = true;
        $email = $result->fetch_assoc()['email'];
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && $valid_token) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update password
        $update_sql = "UPDATE users SET password = ? WHERE email = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $email);
        
        if($update_stmt->execute()) {
            // Mark token as used
            $token_sql = "UPDATE password_resets SET used = 1 WHERE token = ?";
            $token_stmt = $conn->prepare($token_sql);
            $token_stmt->bind_param("s", $token);
            $token_stmt->execute();
            
            $_SESSION['success'] = "Password has been reset successfully. Please login with your new password.";
            header('Location: /ram/login.php');
            exit();
        } else {
            $error = "Failed to update password. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Reset Password</h3>
                    
                    <?php if(!$valid_token): ?>
                        <div class="alert alert-danger">
                            Invalid or expired reset link. Please request a new password reset.
                        </div>
                        <div class="text-center mt-3">
                            <a href="/ram/forgot_password.php" class="btn btn-danger">Request New Reset Link</a>
                        </div>
                    <?php else: ?>
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="resetPasswordForm">
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger">Reset Password</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add loading overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <img src="assets/loading.gif" alt="Loading...">
    </div>
</div>

<script>
if(document.getElementById('resetPasswordForm')) {
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        document.getElementById('loadingOverlay').style.display = 'block';
        setTimeout(function() {
            e.target.submit();
        }, 2000);
        e.preventDefault();
    });
}
</script>

<?php include 'includes/footer.php'; ?> 