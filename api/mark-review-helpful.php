<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please login'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$reviewId = $data['review_id'] ?? 0;

if (empty($reviewId)) {
    jsonResponse(['success' => false, 'message' => 'Invalid review ID']);
}

try {
    // Check if already voted
    $stmt = $db->prepare("SELECT id FROM review_helpful_votes WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$reviewId, getUserId()]);
    
    if ($stmt->fetch()) {
        // Remove vote
        $stmt = $db->prepare("DELETE FROM review_helpful_votes WHERE review_id = ? AND user_id = ?");
        $stmt->execute([$reviewId, getUserId()]);
        $action = 'removed';
    } else {
        // Add vote
        $stmt = $db->prepare("INSERT INTO review_helpful_votes (review_id, user_id) VALUES (?, ?)");
        $stmt->execute([$reviewId, getUserId()]);
        $action = 'added';
    }
    
    // Update helpful count
    $stmt = $db->prepare("
        UPDATE product_reviews 
        SET helpful_count = (SELECT COUNT(*) FROM review_helpful_votes WHERE review_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$reviewId, $reviewId]);
    
    // Get new count
    $stmt = $db->prepare("SELECT helpful_count FROM product_reviews WHERE id = ?");
    $stmt->execute([$reviewId]);
    $count = $stmt->fetchColumn();
    
    jsonResponse([
        'success' => true,
        'action' => $action,
        'count' => $count
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to update vote'], 500);
}
?>
