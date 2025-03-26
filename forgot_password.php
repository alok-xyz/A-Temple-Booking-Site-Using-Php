<?php
require_once 'config/config.php';
require 'vendor/autoload.php'; // Make sure PHPMailer is installed via composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if(isLoggedIn()) {
    header('Location: /ram/dashboard.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+5 hours'));
        
        // Save token to database
        $token_sql = "INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)";
        $token_stmt = $conn->prepare($token_sql);
        $token_stmt->bind_param("sss", $email, $token, $expires);
        
        if($token_stmt->execute()) {
            // Send email
            $mail = new PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'photoreminder701@gmail.com';
                $mail->Password = 'qmtq bosz kjcq cilg';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                $mail->setFrom('photoreminder701@gmail.com', 'Ram Janmabhoomi');
                $mail->addAddress($email);
                
                $reset_link = "http://{$_SERVER['HTTP_HOST']}/ram/reset_password.php?token=" . $token;
                
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
                    <h2>Password Reset Request</h2>
                    <p>Click the link below to reset your password. This link will expire in 5 hours.</p>
                    <p><a href='{$reset_link}'>Reset Password</a></p>
                    <p>If you didn't request this, please ignore this email.</p>
                ";
                
                $mail->send();
                $success = "Password reset instructions have been sent to your email.";
            } catch (Exception $e) {
                $error = "Email could not be sent. Please try again later.";
            }
        } else {
            $error = "Something went wrong. Please try again.";
        }
    } else {
        $error = "No account found with that email address.";
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Forgot Password</h3>
                    
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <small class="text-muted">Enter your registered email address to receive password reset instructions.</small>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger">Send Reset Link</button>
                            <a href="/ram/login.php" class="btn btn-light">Back to Login</a>
                        </div>
                    </form>
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
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    document.getElementById('loadingOverlay').style.display = 'block';
    setTimeout(function() {
        e.target.submit();
    }, 2000);
    e.preventDefault();
});
</script>

<?php include 'includes/footer.php'; ?> 