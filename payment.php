<?php
require_once 'config/config.php';
require_once 'config/payment.php';

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

$pageTitle = 'Payment';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-credit-card fa-4x text-primary"></i>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Complete Your Payment</h2>
                    <p class="text-muted mb-4">Order #<?php echo $order['order_number']; ?></p>
                    
                    <div class="bg-light rounded p-4 mb-4">
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
                            <strong>Total Amount:</strong>
                            <strong class="text-primary fs-4"><?php echo formatPrice($order['final_amount']); ?></strong>
                        </div>
                    </div>
                    
                    <button id="payNowBtn" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-lock me-2"></i>Pay Securely with Razorpay
                    </button>
                    
                    <div class="text-muted small">
                        <i class="fas fa-shield-alt me-1"></i>
                        Your payment information is secure and encrypted
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payNowBtn').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    // Create payment order
    fetch('api/create-payment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({order_id: <?php echo $orderId; ?>})
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            throw new Error(data.message);
        }
        
        // Initialize Razorpay
        const options = {
            key: data.key,
            amount: data.amount,
            currency: data.currency,
            name: data.name,
            description: data.description,
            order_id: data.order_id,
            prefill: data.prefill,
            theme: {
                color: '#1266f1'
            },
            handler: function(response) {
                // Verify payment
                fetch('api/verify-payment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        gateway: 'razorpay',
                        order_id: <?php echo $orderId; ?>,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_signature: response.razorpay_signature
                    })
                })
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        window.location.href = result.redirect;
                    } else {
                        alert('Payment verification failed: ' + result.message);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay Securely with Razorpay';
                    }
                });
            },
            modal: {
                ondismiss: function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay Securely with Razorpay';
                }
            }
        };
        
        const rzp = new Razorpay(options);
        rzp.open();
    })
    .catch(error => {
        alert('Error: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock me-2"></i>Pay Securely with Razorpay';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
