<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'My Orders';

// Fetch user orders
$stmt = $db->prepare("
    SELECT o.*, 
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([getUserId()]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container my-4">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-shopping-bag text-primary"></i> My Orders & Downloads
    </h1>
    
    <?php if (empty($orders)): ?>
    <div class="card shadow-custom rounded-custom">
        <div class="card-body text-center py-5">
            <i class="fas fa-shopping-bag fa-5x text-muted mb-4"></i>
            <h3>No orders yet</h3>
            <p class="text-muted mb-4">Start shopping to see your orders here!</p>
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i> Browse Products
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($orders as $order): ?>
        <div class="col-12">
            <div class="card order-card shadow-custom">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <h6 class="mb-1">Order #<?php echo $order['order_number']; ?></h6>
                            <small class="text-muted">
                                <i class="far fa-calendar me-1"></i>
                                <?php echo formatDate($order['created_at']); ?>
                            </small>
                        </div>
                        
                        <div class="col-md-2 mb-3 mb-md-0">
                            <small class="text-muted d-block">Items</small>
                            <strong><?php echo $order['item_count']; ?> Product(s)</strong>
                        </div>
                        
                        <div class="col-md-2 mb-3 mb-md-0">
                            <small class="text-muted d-block">Total</small>
                            <strong class="text-primary"><?php echo formatPrice($order['final_amount']); ?></strong>
                        </div>
                        
                        <div class="col-md-2 mb-3 mb-md-0">
                            <small class="text-muted d-block">Payment</small>
                            <span class="order-status status-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                        
                        <div class="col-md-3 text-md-end">
                            <a href="order-detail.php?order=<?php echo $order['order_number']; ?>" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>
                            <?php if ($order['payment_status'] === 'completed'): ?>
                            <a href="order-detail.php?order=<?php echo $order['order_number']; ?>#downloads" 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
