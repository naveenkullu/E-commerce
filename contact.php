<?php
require_once 'config/config.php';
$pageTitle = 'Contact Us';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $userId = isLoggedIn() ? getUserId() : null;
        
        $stmt = $db->prepare("
            INSERT INTO support_tickets (user_id, name, email, subject, message, status)
            VALUES (?, ?, ?, ?, ?, 'open')
        ");
        
        if ($stmt->execute([$userId, $name, $email, $subject, $message])) {
            $success = 'Your message has been sent successfully! We will get back to you soon.';
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <h1 class="fw-bold mb-3">Get In Touch</h1>
            <p class="lead text-muted">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Contact Form -->
        <div class="col-lg-8">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Send us a Message</h4>
                    
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
                    
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="text" id="name" name="name" class="form-control" required
                                           value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['name']) : (htmlspecialchars($_POST['name'] ?? '')); ?>">
                                    <label class="form-label" for="name">Your Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="email" id="email" name="email" class="form-control" required
                                           value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['email']) : (htmlspecialchars($_POST['email'] ?? '')); ?>">
                                    <label class="form-label" for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-outline">
                                    <input type="text" id="subject" name="subject" class="form-control" required
                                           value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                                    <label class="form-label" for="subject">Subject</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-outline">
                                    <textarea id="message" name="message" class="form-control" rows="6" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                    <label class="form-label" for="message">Message</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Contact Info -->
        <div class="col-lg-4">
            <div class="card shadow-custom rounded-custom mb-4">
                <div class="card-body text-center p-4">
                    <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Email Us</h5>
                    <p class="text-muted"><?php echo getSetting('site_email') ?? 'info@marketplace.com'; ?></p>
                </div>
            </div>
            
            <div class="card shadow-custom rounded-custom mb-4">
                <div class="card-body text-center p-4">
                    <i class="fas fa-phone fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Call Us</h5>
                    <p class="text-muted">+1 234 567 8900</p>
                </div>
            </div>
            
            <div class="card shadow-custom rounded-custom">
                <div class="card-body text-center p-4">
                    <i class="fas fa-clock fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Business Hours</h5>
                    <p class="text-muted mb-1">Monday - Friday</p>
                    <p class="text-muted">9:00 AM - 6:00 PM</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
