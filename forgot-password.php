<?php
require_once 'config/config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Forgot Password';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = generateToken(32);
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $stmt->execute([$token, $expiry, $user['id']]);
            
            // Send reset email (simplified - integrate with PHPMailer for production)
            $resetLink = BASE_URL . "reset-password.php?token=" . $token;
            $subject = "Password Reset Request";
            $message = "Click the following link to reset your password: " . $resetLink;
            
            // In production, use proper email sending
            // sendEmail($email, $subject, $message);
            
            $success = 'Password reset instructions have been sent to your email address.';
        } else {
            // Don't reveal if email exists for security
            $success = 'If an account exists with this email, password reset instructions have been sent.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-key fa-4x text-primary mb-3"></i>
                        <h2 class="fw-bold">Forgot Password?</h2>
                        <p class="text-muted">Enter your email to reset your password</p>
                    </div>
                    
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
                    
                    <form method="POST" action="">
                        <div class="form-outline mb-4">
                            <input type="email" id="email" name="email" class="form-control form-control-lg" required>
                            <label class="form-label" for="email">Email Address</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block w-100 mb-3">
                            <i class="fas fa-paper-plane me-2"></i> Send Reset Link
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                Remember your password? 
                                <a href="login.php" class="text-primary fw-bold">Login</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
