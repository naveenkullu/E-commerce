<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$orderId = $_GET['order'] ?? 0;

// Fetch order
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, getUserId()]);
$order = $stmt->fetch();

if (!$order) {
    redirect('cart.php');
}

if ($order['payment_status'] === 'completed') {
    redirect('order-success.php?order=' . $order['order_number']);
}

$pageTitle = 'Payment Instructions';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-money-bill-wave fa-4x text-success mb-3"></i>
                        <h2 class="fw-bold">Payment Instructions</h2>
                        <p class="text-muted">Order #<?php echo $order['order_number']; ?></p>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Your order has been created successfully! Please complete the payment to access your products.
                    </div>
                    
                    <div class="bg-light rounded p-4 mb-4">
                        <h5 class="fw-bold mb-3">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount:</span>
                            <span>-<?php echo formatPrice($order['discount_amount']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['tax_amount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span><?php echo formatPrice($order['tax_amount']); ?></span>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong class="fs-5">Total Amount:</strong>
                            <strong class="text-primary fs-4"><?php echo formatPrice($order['final_amount']); ?></strong>
                        </div>
                    </div>
                    
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-university me-2"></i>Bank Transfer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Bank Name:</strong>
                                    <p class="mb-0">State Bank of India</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Account Name:</strong>
                                    <p class="mb-0">YBT Digital</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Account Number:</strong>
                                    <p class="mb-0">1234567890</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>IFSC Code:</strong>
                                    <p class="mb-0">SBIN0001234</p>
                                </div>
                                <div class="col-12">
                                    <strong>UPI ID:</strong>
                                    <p class="mb-0">ybtdigital@upi</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Important Instructions:</h6>
                        <ol class="mb-0 ps-3">
                            <li>Transfer the exact amount: <strong><?php echo formatPrice($order['final_amount']); ?></strong></li>
                            <li>Use Order Number as reference: <strong><?php echo $order['order_number']; ?></strong></li>
                            <li>After payment, send screenshot to: <strong><?php echo getSetting('site_email'); ?></strong></li>
                            <li>Payment will be verified within 24 hours</li>
                            <li>You'll receive email confirmation once verified</li>
                        </ol>
                    </div>
                    
                    <div class="text-center">
                        <a href="my-orders.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-list me-2"></i>View My Orders
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary btn-lg ms-2">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                    
                    <div class="text-center mt-4 text-muted small">
                        <i class="fas fa-shield-alt me-1"></i>
                        Your order is secure. We'll notify you once payment is confirmed.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
