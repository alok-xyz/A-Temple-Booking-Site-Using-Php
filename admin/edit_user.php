<?php
require_once '../config/config.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: /ram/admin/login.php');
    exit();
}

$user_id = $_GET['id'] ?? 0;

// Fetch user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if(!$user) {
    header('Location: /ram/admin/users.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    
    // Check if email exists for other users
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    
    if($check_stmt->get_result()->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $phone, $role, $user_id);
        
        if($stmt->execute()) {
            // If password is provided, update it
            if(!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $pass_sql = "UPDATE users SET password = ? WHERE id = ?";
                $pass_stmt = $conn->prepare($pass_sql);
                $pass_stmt->bind_param("si", $password, $user_id);
                $pass_stmt->execute();
            }
            
            $_SESSION['success'] = "User updated successfully!";
            header('Location: /ram/admin/users.php');
            exit();
        } else {
            $error = "Failed to update user. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Edit User</h2>
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
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo $user['name']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?php echo $user['email']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" 
                                   value="<?php echo $user['phone']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" name="password">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-control" name="role" required>
                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>
                                    User
                                </option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>
                                    Admin
                                </option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/ram/admin/users.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 