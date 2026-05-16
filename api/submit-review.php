<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please login to submit a review'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$productId = $data['product_id'] ?? 0;
$rating = $data['rating'] ?? 0;
$title = sanitize($data['title'] ?? '');
$review = sanitize($data['review'] ?? '');

// Validation
if (empty($productId) || empty($rating) || empty($review)) {
    jsonResponse(['success' => false, 'message' => 'Please fill in all required fields']);
}

if ($rating < 1 || $rating > 5) {
    jsonResponse(['success' => false, 'message' => 'Rating must be between 1 and 5']);
}

// Check if product exists
$stmt = $db->prepare("SELECT id FROM products WHERE id = ? AND status = 'active'");
$stmt->execute([$productId]);
if (!$stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => 'Product not found']);
}

// Check if user already reviewed this product
$stmt = $db->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
$stmt->execute([$productId, getUserId()]);
if ($stmt->fetch()) {
    jsonResponse(['success' => false, 'message' => 'You have already reviewed this product']);
}

// Check if user purchased this product
$stmt = $db->prepare("
    SELECT o.id 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'completed'
    LIMIT 1
");
$stmt->execute([getUserId(), $productId]);
$order = $stmt->fetch();
$isVerifiedPurchase = $order ? true : false;
$orderId = $order ? $order['id'] : null;

try {
    // Insert review
    $stmt = $db->prepare("
        INSERT INTO product_reviews (product_id, user_id, order_id, rating, title, review, is_verified_purchase, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')
    ");
    $stmt->execute([$productId, getUserId(), $orderId, $rating, $title, $review, $isVerifiedPurchase]);
    
    // Update product average rating
    $stmt = $db->prepare("
        UPDATE products 
        SET average_rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = ? AND status = 'approved'),
            total_reviews = (SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND status = 'approved')
        WHERE id = ?
    ");
    $stmt->execute([$productId, $productId, $productId]);
    
    jsonResponse([
        'success' => true, 
        'message' => 'Thank you for your review!',
        'is_verified' => $isVerifiedPurchase
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to submit review. Please try again.'], 500);
}
?>
