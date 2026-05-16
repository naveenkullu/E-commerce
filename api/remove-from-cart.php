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

// Initialize cart in session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove from cart
$key = array_search($productId, $_SESSION['cart']);
if ($key !== false) {
    unset($_SESSION['cart'][$key]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
    
    echo json_encode([
        'success' => true,
        'message' => 'Product removed from cart',
        'cart_count' => count($_SESSION['cart'])
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Product not in cart']);
}
?>
