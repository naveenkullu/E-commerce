<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['product_id'] ?? 0;

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if product exists and is active
$stmt = $db->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if already in cart
if (in_array($productId, $_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Product already in cart']);
    exit;
}

// Add to cart
$_SESSION['cart'][] = $productId;

echo json_encode([
    'success' => true,
    'message' => 'Product added to cart',
    'cart_count' => count($_SESSION['cart'])
]);
?>
