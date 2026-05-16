<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Order Details';
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    redirect('orders.php');
}

// Fetch order details
$stmt = $db->prepare("
    SELECT o.* 
    FROM orders o
    WHERE o.order_number = ? AND o.user_id = ?
");
$stmt->execute([$orderNumber, getUserId()]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}

// Fetch order items with download info
$stmt = $db->prepare("
    SELECT oi.*, p.id as product_id,
           (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image,
           d.download_token, d.download_count, d.max_downloads, d.expiry_date
    FROM order_items oi
    INNER JOIN products p ON oi.product_id = p.id
    LEFT JOIN downloads d ON d.product_id = p.id AND d.order_id = ? AND d.user_id = ?
    WHERE oi.order_id = ?
");
$stmt->execute([$order['id'], getUserId(), $order['id']]);
$orderItems = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-receipt text-primary"></i> Order Details
        </h1>
        <a href="orders.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Orders
        </a>
    </div>
    
    <div class="row g-4">
        <!-- Order Information -->
        <div class="col-lg-8">
            <div class="card shadow-custom rounded-custom mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h5 class="card-title mb-2">Order #<?php echo $order['order_number']; ?></h5>
                            <p class="text-muted mb-0">
                                <i class="far fa-calendar me-2"></i>
                                <?php echo formatDateTime($order['created_at']); ?>
                            </p>
                        </div>
                        <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : 'warning'; ?> fs-6">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Order Items</h6>
                    <?php foreach ($orderItems as $item): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="<?php echo $item['image'] ? BASE_URL . 'uploads/screenshots/' . $item['image'] : 'https://via.placeholder.com/100'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                         class="img-fluid rounded">
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['product_title']); ?></h6>
                                    <p class="text-muted small mb-0">Quantity: <?php echo $item['quantity']; ?></p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <strong class="text-primary"><?php echo formatPrice($item['price']); ?></strong>
                                </div>
                                <div class="col-md-2">
                                    <?php if ($order['payment_status'] === 'completed' && $item['download_token']): ?>
                                    <button class="btn btn-success btn-sm w-100" 
                                            onclick="downloadProduct('<?php echo $item['download_token']; ?>', this)">
                                        <i class="fas fa-download me-1"></i> Download
                                    </button>
                                    <small class="text-muted d-block mt-1">
                                        <?php echo $item['download_count']; ?> / <?php echo $item['max_downloads']; ?> downloads
                                    </small>
                                    <?php else: ?>
                                    <span class="badge bg-warning">Pending Payment</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ($order['payment_status'] === 'completed'): ?>
            <div class="alert alert-info" id="downloads">
                <h6><i class="fas fa-info-circle me-2"></i> Download Information</h6>
                <ul class="mb-0">
                    <li>Your downloads are available for <?php echo getSetting('download_expiry_days') ?? 30; ?> days</li>
                    <li>Each product can be downloaded up to <?php echo getSetting('max_downloads_per_product') ?? 5; ?> times</li>
                    <li>Keep your download links secure and do not share them</li>
                </ul>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-custom rounded-custom mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Order Summary</h5>
                    
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
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5">Total:</span>
                        <span class="h5 text-primary fw-bold"><?php echo formatPrice($order['final_amount']); ?></span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Payment Method</small>
                        <strong><?php echo strtoupper($order['payment_gateway']); ?></strong>
                    </div>
                    
                    <?php if ($order['transaction_id']): ?>
                    <div class="mb-3">
                        <small class="text-muted d-block">Transaction ID</small>
                        <code><?php echo htmlspecialchars($order['transaction_id']); ?></code>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow-custom rounded-custom">
                <div class="card-body">
                    <h6 class="card-title mb-3">Need Help?</h6>
                    <p class="small text-muted">If you have any questions about your order, please contact our support team.</p>
                    <a href="contact.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-headset me-2"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
