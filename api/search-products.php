<?php
require_once '../config/config.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['products' => []]);
    exit;
}

// Search products
$stmt = $db->prepare("
    SELECT p.id, p.title, p.price,
           (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image
    FROM products p
    WHERE p.status = 'active' 
    AND (p.title LIKE ? OR p.description LIKE ?)
    ORDER BY p.views DESC
    LIMIT 10
");

$searchTerm = "%$query%";
$stmt->execute([$searchTerm, $searchTerm]);
$products = $stmt->fetchAll();

// Format results
$results = [];
foreach ($products as $product) {
    $results[] = [
        'id' => $product['id'],
        'title' => $product['title'],
        'price' => formatPrice($product['price']),
        'image' => $product['image'] ? BASE_URL . 'uploads/screenshots/' . $product['image'] : 'https://via.placeholder.com/50'
    ];
}

echo json_encode(['products' => $results]);
?>
