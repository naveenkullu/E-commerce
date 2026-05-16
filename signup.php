<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$pageTitle = 'Sign Up';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email address already registered';
        } else {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user') RETURNING id");
            
            if ($stmt->execute([$name, $email, $hashedPassword])) {
                $success = 'Account created successfully! Please login.';
                // Auto login
                $userId = $stmt->fetchColumn();
                $_SESSION['user_id'] = $userId;
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';
                
                // Redirect to home page
                header("refresh:2;url=" . BASE_URL . "index.php");
            } else {
                $error = 'Failed to create account. Please try again.';
            }
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
                        <i class="fas fa-user-plus fa-4x text-primary mb-3"></i>
                        <h2 class="fw-bold">Create Account</h2>
                        <p class="text-muted">Join our digital marketplace</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="form-outline mb-4">
                            <input type="text" id="name" name="name" class="form-control form-control-lg" required 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            <label class="form-label" for="name">Full Name</label>
                            <div class="invalid-feedback">Please enter your name</div>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="email" id="email" name="email" class="form-control form-control-lg" required
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            <label class="form-label" for="email">Email Address</label>
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" id="password" name="password" class="form-control form-control-lg" 
                                   required minlength="6">
                            <label class="form-label" for="password">Password</label>
                            <div class="invalid-feedback">Password must be at least 6 characters</div>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   class="form-control form-control-lg" required minlength="6">
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                            <div class="invalid-feedback">Please confirm your password</div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-primary">Terms and Conditions</a>
                            </label>
                            <div class="invalid-feedback">You must agree to the terms</div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block w-100 mb-3">
                            <i class="fas fa-user-plus me-2"></i> Create Account
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-primary fw-bold">Login</a></p>
                        </div>
                    </form>
                    
                    <div class="divider d-flex align-items-center my-4">
                        <p class="text-center fw-bold mx-3 mb-0 text-muted">OR</p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" type="button">
                            <i class="fab fa-google me-2"></i> Sign up with Google
                        </button>
                        <button class="btn btn-outline-dark" type="button">
                            <i class="fab fa-facebook me-2"></i> Sign up with Facebook
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
