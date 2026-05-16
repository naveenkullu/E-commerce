<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Order Success';
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    redirect('orders.php');
}

// Fetch order details
$stmt = $db->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.order_number = ? AND o.user_id = ?
    GROUP BY o.id
");
$stmt->execute([$orderNumber, getUserId()]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

// Fetch order items
$stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$orderItems = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="card shadow-custom rounded-custom text-center mb-4">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>
                    <h1 class="fw-bold mb-3">Order Placed Successfully!</h1>
                    <p class="lead text-muted mb-4">
                        Thank you for your purchase. Your order has been received and is being processed.
                    </p>
                    <div class="alert alert-info">
                        <strong>Order Number:</strong> <?php echo $order['order_number']; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Details -->
            <div class="card shadow-custom rounded-custom mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Order Details</h5>
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <small class="text-muted">Order Date</small>
                            <p class="mb-0 fw-bold"><?php echo formatDateTime($order['created_at']); ?></p>
                        </div>
                        <div class="col-6 text-end">
                            <small class="text-muted">Payment Status</small>
                            <p class="mb-0">
                                <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Items Ordered (<?php echo $order['item_count']; ?>)</h6>
                    <?php foreach ($orderItems as $item): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <strong><?php echo htmlspecialchars($item['product_title']); ?></strong>
                            <br><small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                        </div>
                        <strong class="text-primary"><?php echo formatPrice($item['price']); ?></strong>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold"><?php echo formatPrice($order['total_amount']); ?></span>
                    </div>
                    
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount:</span>
                        <span class="fw-bold">-<?php echo formatPrice($order['discount_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($order['tax_amount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax:</span>
                        <span class="fw-bold"><?php echo formatPrice($order['tax_amount']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <span class="h5">Total:</span>
                        <span class="h5 text-primary fw-bold"><?php echo formatPrice($order['final_amount']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Next Steps -->
            <div class="card shadow-custom rounded-custom mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">What's Next?</h5>
                    
                    <?php if ($order['payment_status'] === 'completed'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Payment Confirmed!</strong> You can now download your products.
                    </div>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Your payment has been processed successfully
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Download links are now available in your orders page
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            A confirmation email has been sent to your email address
                        </li>
                    </ul>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Payment Pending!</strong> Your order is awaiting payment confirmation.
                    </div>
                    
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning me-2"></i>
                            Your payment is being processed
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning me-2"></i>
                            You will receive an email once payment is confirmed
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning me-2"></i>
                            Downloads will be available after payment confirmation
                        </li>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <?php if ($order['payment_status'] === 'completed'): ?>
                <a href="orders.php" class="btn btn-success btn-lg">
                    <i class="fas fa-download me-2"></i> View Downloads
                </a>
                <?php endif; ?>
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                </a>
                <a href="orders.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-list me-2"></i> View All Orders
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
