<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$couponCode = strtoupper(trim($data['coupon_code'] ?? ''));

if (empty($couponCode)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
    exit;
}

// Check if cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty']);
    exit;
}

// Calculate cart total
$placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
$stmt = $db->prepare("SELECT SUM(price) as total FROM products WHERE id IN ($placeholders) AND status = 'active'");
$stmt->execute($_SESSION['cart']);
$result = $stmt->fetch();
$cartTotal = $result['total'] ?? 0;

// Fetch coupon
$stmt = $db->prepare("
    SELECT * FROM coupons 
    WHERE code = ? 
    AND status = 'active'
    AND (expiry_date IS NULL OR expiry_date > CURRENT_TIMESTAMP)
    AND (usage_limit IS NULL OR used_count < usage_limit)
");
$stmt->execute([$couponCode]);
$coupon = $stmt->fetch();

if (!$coupon) {
    echo json_encode(['success' => false, 'message' => 'Invalid or expired coupon code']);
    exit;
}

// Check minimum purchase
if ($coupon['min_purchase'] > 0 && $cartTotal < $coupon['min_purchase']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Minimum purchase of ' . formatPrice($coupon['min_purchase']) . ' required'
    ]);
    exit;
}

// Calculate discount
if ($coupon['type'] === 'flat') {
    $discountAmount = $coupon['value'];
} else {
    $discountAmount = ($cartTotal * $coupon['value']) / 100;
}

// Apply max discount limit
if ($coupon['max_discount'] && $discountAmount > $coupon['max_discount']) {
    $discountAmount = $coupon['max_discount'];
}

// Ensure discount doesn't exceed cart total
if ($discountAmount > $cartTotal) {
    $discountAmount = $cartTotal;
}

// Store in session
$_SESSION['coupon_code'] = $couponCode;
$_SESSION['coupon_id'] = $coupon['id'];
$_SESSION['discount_amount'] = $discountAmount;

echo json_encode([
    'success' => true,
    'message' => 'Coupon applied successfully!',
    'discount_amount' => formatPrice($discountAmount),
    'new_total' => formatPrice($cartTotal - $discountAmount)
]);
?>
