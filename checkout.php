<?php
require_once 'config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Checkout';

// Initialize cart
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('cart.php');
}

// Fetch cart products
$placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
$stmt = $db->prepare("
    SELECT p.*,
           (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image
    FROM products p
    WHERE p.id IN ($placeholders) AND p.status = 'active'
");
$stmt->execute($_SESSION['cart']);
$cartProducts = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cartProducts as $product) {
    $subtotal += $product['price'];
}

$discountAmount = $_SESSION['discount_amount'] ?? 0;
$taxPercentage = getSetting('tax_percentage') ?? 0;
$taxAmount = (($subtotal - $discountAmount) * $taxPercentage) / 100;
$total = $subtotal - $discountAmount + $taxAmount;

// Get payment gateway
$paymentGateway = getSetting('payment_gateway') ?? 'razorpay';

// Process order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Generate order number
        $orderNumber = 'ORD-' . strtoupper(uniqid());
        
        // Create order
        $stmt = $db->prepare("
            INSERT INTO orders (user_id, order_number, total_amount, discount_amount, tax_amount, final_amount, 
                               payment_method, payment_gateway, payment_status, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
            RETURNING id
        ");
        $stmt->execute([
            getUserId(),
            $orderNumber,
            $subtotal,
            $discountAmount,
            $taxAmount,
            $total,
            $paymentGateway,
            $paymentGateway
        ]);
        
        $orderId = $stmt->fetchColumn();
        
        // Add order items
        $stmt = $db->prepare("
            INSERT INTO order_items (order_id, product_id, product_title, price, quantity)
            VALUES (?, ?, ?, ?, 1)
        ");
        
        foreach ($cartProducts as $product) {
            $stmt->execute([
                $orderId,
                $product['id'],
                $product['title'],
                $product['price']
            ]);
        }
        
        $db->commit();
        
        // Redirect to manual payment page (no API needed)
        redirect('payment-manual.php?order=' . $orderId);
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Failed to process order. Please try again.';
    }
}

include 'includes/header.php';
?>

<div class="container my-4">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-lock text-primary"></i> Secure Checkout
    </h1>
    
    <?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="row g-4">
        <!-- Checkout Form -->
        <div class="col-lg-8">
            <form method="POST" action="" id="checkoutForm">
                <!-- Billing Information -->
                <div class="card shadow-custom rounded-custom mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-user me-2"></i> Billing Information
                        </h5>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="text" id="firstName" class="form-control" required 
                                           value="<?php echo htmlspecialchars($_SESSION['name']); ?>">
                                    <label class="form-label" for="firstName">Full Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="email" id="email" class="form-control" required readonly
                                           value="<?php echo htmlspecialchars($_SESSION['email']); ?>">
                                    <label class="form-label" for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-outline">
                                    <input type="text" id="address" class="form-control">
                                    <label class="form-label" for="address">Address (Optional)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="text" id="city" class="form-control">
                                    <label class="form-label" for="city">City (Optional)</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-outline">
                                    <input type="text" id="country" class="form-control">
                                    <label class="form-label" for="country">Country (Optional)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="card shadow-custom rounded-custom mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-credit-card me-2"></i> Payment Method
                        </h5>
                        
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="razorpay" 
                                   value="razorpay" <?php echo $paymentGateway === 'razorpay' ? 'checked' : ''; ?>>
                            <label class="form-check-label d-flex align-items-center" for="razorpay">
                                <i class="fas fa-wallet fa-2x text-primary me-3"></i>
                                <div>
                                    <strong>Razorpay</strong>
                                    <p class="mb-0 small text-muted">Credit/Debit Card, UPI, Net Banking</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="stripe" 
                                   value="stripe" <?php echo $paymentGateway === 'stripe' ? 'checked' : ''; ?>>
                            <label class="form-check-label d-flex align-items-center" for="stripe">
                                <i class="fab fa-stripe fa-2x text-info me-3"></i>
                                <div>
                                    <strong>Stripe</strong>
                                    <p class="mb-0 small text-muted">Credit/Debit Card</p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-check mb-3 p-3 border rounded">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="paypal" 
                                   value="paypal" <?php echo $paymentGateway === 'paypal' ? 'checked' : ''; ?>>
                            <label class="form-check-label d-flex align-items-center" for="paypal">
                                <i class="fab fa-paypal fa-2x text-primary me-3"></i>
                                <div>
                                    <strong>PayPal</strong>
                                    <p class="mb-0 small text-muted">PayPal Account</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Terms and Conditions -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" class="text-primary">Terms and Conditions</a> and 
                        <a href="#" class="text-primary">Refund Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-success btn-lg w-100">
                    <i class="fas fa-lock me-2"></i> Place Order - <?php echo formatPrice($total); ?>
                </button>
            </form>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-custom rounded-custom sticky-top" style="top: 80px;">
                <div class="card-body">
                    <h5 class="card-title mb-4">Order Summary</h5>
                    
                    <!-- Products List -->
                    <div class="mb-3">
                        <?php foreach ($cartProducts as $product): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <img src="<?php echo $product['image'] ? BASE_URL . 'uploads/screenshots/' . $product['image'] : 'https://via.placeholder.com/60'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>"
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                 class="me-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 small"><?php echo htmlspecialchars($product['title']); ?></h6>
                                <small class="text-primary"><?php echo formatPrice($product['price']); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
                    <!-- Price Breakdown -->
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold"><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    
                    <?php if ($discountAmount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount:</span>
                        <span class="fw-bold">-<?php echo formatPrice($discountAmount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($taxPercentage > 0): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (<?php echo $taxPercentage; ?>%):</span>
                        <span class="fw-bold"><?php echo formatPrice($taxAmount); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5">Total:</span>
                        <span class="h5 text-primary fw-bold"><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <!-- Security Badges -->
                    <div class="text-center">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-shield-alt me-1"></i> 100% Secure Payment
                        </small>
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-lock me-1"></i> SSL Encrypted
                        </small>
                        <small class="text-muted d-block">
                            <i class="fas fa-undo me-1"></i> 30-Day Money Back
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
