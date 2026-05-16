<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Shopping Cart';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Fetch cart products
$cartProducts = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $stmt = $db->prepare("
        SELECT p.*,
               (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image
        FROM products p
        WHERE p.id IN ($placeholders) AND p.status = 'active'
    ");
    $stmt->execute($_SESSION['cart']);
    $cartProducts = $stmt->fetchAll();
    
    foreach ($cartProducts as $product) {
        $subtotal += $product['price'];
    }
}

// Calculate tax
$taxPercentage = getSetting('tax_percentage') ?? 0;
$taxAmount = ($subtotal * $taxPercentage) / 100;
$total = $subtotal + $taxAmount;

include 'includes/header.php';
?>

<div class="container my-4">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-shopping-cart text-primary"></i> Shopping Cart
    </h1>
    
    <?php if (empty($cartProducts)): ?>
    <div class="card shadow-custom rounded-custom">
        <div class="card-body text-center py-5">
            <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted mb-4">Add some products to get started!</p>
            <a href="products.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i> Browse Products
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body">
                    <h5 class="card-title mb-4">Cart Items (<?php echo count($cartProducts); ?>)</h5>
                    
                    <?php foreach ($cartProducts as $product): ?>
                    <div class="cart-item border-bottom pb-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-2 col-3">
                                <img src="<?php echo $product['image'] ? BASE_URL . 'uploads/screenshots/' . $product['image'] : 'https://via.placeholder.com/100'; ?>" 
                                     class="cart-item-img" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>">
                            </div>
                            <div class="col-md-5 col-9">
                                <h6 class="mb-1">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($product['title']); ?>
                                    </a>
                                </h6>
                                <p class="text-muted small mb-0">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?>
                                </p>
                            </div>
                            <div class="col-md-3 col-6 mt-2 mt-md-0">
                                <h5 class="text-primary mb-0">
                                    <?php echo formatPrice($product['price']); ?>
                                </h5>
                            </div>
                            <div class="col-md-2 col-6 mt-2 mt-md-0 text-end">
                                <button class="btn btn-danger btn-sm" 
                                        onclick="removeFromCart(<?php echo $product['id']; ?>, this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-custom rounded-custom sticky-top" style="top: 80px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">Order Summary</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    
                    <?php if ($taxPercentage > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (<?php echo $taxPercentage; ?>%):</span>
                        <span class="fw-bold"><?php echo formatPrice($taxAmount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5">Total:</span>
                        <span class="h5 text-primary fw-bold cart-total"><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <!-- Coupon Code -->
                    <div class="mb-4">
                        <label class="form-label">Have a coupon?</label>
                        <div class="input-group">
                            <input type="text" id="couponCode" class="form-control" placeholder="Enter code">
                            <button class="btn btn-outline-primary" type="button" id="applyCouponBtn" onclick="applyCoupon()">
                                Apply
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="checkout.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-lock me-2"></i> Proceed to Checkout
                        </a>
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                        </a>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i> Secure Checkout
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
