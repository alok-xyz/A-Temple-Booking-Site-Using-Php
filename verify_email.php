<?php
require_once 'config/config.php';

if(!isset($_SESSION['registration_data'])) {
    header('Location: /ram/register.php');
    exit();
}

$registration_data = $_SESSION['registration_data'];
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp'])) {
    $otp = trim($_POST['otp']);
    $email = $registration_data['email'];
    $user_id = $registration_data['user_id'];

    // Validate OTP format
    if(empty($otp) || strlen($otp) !== 6 || !is_numeric($otp)) {
        $error = "Please enter a valid 6-digit OTP";
    } else {
        $verify_sql = "SELECT * FROM email_verification 
                      WHERE user_id = ? AND email = ? AND otp = ? 
                      AND is_used = 0 AND expires_at > NOW()
                      ORDER BY created_at DESC LIMIT 1";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("iss", $user_id, $email, $otp);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();

        if($result->num_rows > 0) {
            $conn->begin_transaction();
            try {
                // Mark OTP as used
                $update_otp_sql = "UPDATE email_verification SET is_used = 1 WHERE user_id = ? AND otp = ?";
                $update_otp_stmt = $conn->prepare($update_otp_sql);
                $update_otp_stmt->bind_param("is", $user_id, $otp);
                $update_otp_stmt->execute();

                // Mark user as verified
                $update_user_sql = "UPDATE users SET is_verified = 1 WHERE id = ?";
                $update_user_stmt = $conn->prepare($update_user_sql);
                $update_user_stmt->bind_param("i", $user_id);
                $update_user_stmt->execute();

                $conn->commit();
                $success = "Email verified successfully! Redirecting to login page...";
                unset($_SESSION['registration_data']);
                $_SESSION['success'] = "Registration successful! Please login.";
                
                // Use JavaScript to show success message and redirect
                echo "<script>
                    alert('Email verified successfully! Please login.');
                    window.location.href = '/ram/login.php';
                </script>";
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Verification failed. Please try again.";
            }
        } else {
            $error = "Invalid or expired OTP. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Verify Email</h3>
                    
                    <?php if(isset($error) && !empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <p class="text-center">We've sent an OTP to <?php echo $registration_data['email']; ?></p>
                    <form method="POST" action="" id="otpForm">
                        <div class="mb-3">
                            <label for="otp" class="form-label">Enter OTP</label>
                            <input type="text" class="form-control" id="otp" name="otp" required maxlength="6" pattern="[0-9]{6}">
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="verify_otp" class="btn btn-primary">Verify OTP</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <img src="/ram/assets/loading.gif" alt="Loading...">
    </div>
</div>

<script>
$(document).ready(function() {
    $('#otpForm').on('submit', function(e) {
        e.preventDefault();
        const otpValue = $('#otp').val();
        if(otpValue.length === 6 && !isNaN(otpValue)) {
            $('#loadingOverlay').show();
            this.submit();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?> 