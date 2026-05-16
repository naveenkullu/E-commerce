<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$total = 0;

if (isLoggedIn() && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $placeholders = str_repeat('?,', count($_SESSION['cart']) - 1) . '?';
    $stmt = $db->prepare("SELECT SUM(price) as total FROM products WHERE id IN ($placeholders) AND status = 'active'");
    $stmt->execute($_SESSION['cart']);
    $result = $stmt->fetch();
    $total = $result['total'] ?? 0;
}

echo json_encode([
    'total' => formatPrice($total),
    'raw_total' => $total
]);
?>
