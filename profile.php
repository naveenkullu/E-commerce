<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'My Profile';
$success = '';
$error = '';

// Fetch user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([getUserId()]);
$user = $stmt->fetch();

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($name) || empty($email)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        // Check if email is already taken by another user
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, getUserId()]);
        
        if ($stmt->fetch()) {
            $error = 'Email address already in use';
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, getUserId()])) {
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $success = 'Profile updated successfully!';
                $user['name'] = $name;
                $user['email'] = $email;
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Please fill in all password fields';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $error = 'Current password is incorrect';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashedPassword, getUserId()])) {
            $success = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password';
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-4">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-user-circle text-primary"></i> My Profile
    </h1>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <!-- Profile Card -->
        <div class="col-lg-4">
            <div class="profile-card">
                <div class="profile-header">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&size=100&background=random" 
                         alt="<?php echo htmlspecialchars($user['name']); ?>" 
                         class="profile-avatar">
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Member Since</small>
                        <p class="mb-0 fw-bold"><?php echo formatDate($user['created_at']); ?></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Account Status</small>
                        <p class="mb-0">
                            <span class="badge bg-success">Active</span>
                        </p>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="orders.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i> My Orders
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Forms -->
        <div class="col-lg-8">
            <!-- Update Profile -->
            <div class="card shadow-custom rounded-custom mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-user-edit me-2"></i> Update Profile
                    </h5>
                    
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="text" id="name" name="name" class="form-control" required
                                           value="<?php echo htmlspecialchars($user['name']); ?>">
                                    <label class="form-label" for="name">Full Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="email" id="email" name="email" class="form-control" required
                                           value="<?php echo htmlspecialchars($user['email']); ?>">
                                    <label class="form-label" for="email">Email Address</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card shadow-custom rounded-custom">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-lock me-2"></i> Change Password
                    </h5>
                    
                    <form method="POST" action="">
                        <div class="form-outline mb-4">
                            <input type="password" id="current_password" name="current_password" 
                                   class="form-control" required>
                            <label class="form-label" for="current_password">Current Password</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" id="new_password" name="new_password" 
                                   class="form-control" required minlength="6">
                            <label class="form-label" for="new_password">New Password</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="form-control" required minlength="6">
                            <label class="form-label" for="confirm_password">Confirm New Password</label>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
