<?php
require_once '../config/config.php';

if (!isLoggedIn()) {
    die('Unauthorized access');
}

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Invalid download token');
}

// Verify download token
$stmt = $db->prepare("
    SELECT d.*, p.file_path, p.title
    FROM downloads d
    INNER JOIN products p ON d.product_id = p.id
    WHERE d.download_token = ? AND d.user_id = ?
");
$stmt->execute([$token, getUserId()]);
$download = $stmt->fetch();

if (!$download) {
    die('Invalid or expired download link');
}

// Check expiry
if ($download['expiry_date'] && strtotime($download['expiry_date']) < time()) {
    die('Download link has expired');
}

// Check download limit
if ($download['download_count'] >= $download['max_downloads']) {
    die('Download limit reached');
}

// Check if file exists
$filePath = PRODUCTS_PATH . $download['file_path'];
if (!file_exists($filePath)) {
    die('File not found');
}

// Update download count
$stmt = $db->prepare("
    UPDATE downloads 
    SET download_count = download_count + 1, 
        last_downloaded_at = CURRENT_TIMESTAMP 
    WHERE id = ?
");
$stmt->execute([$download['id']]);

// Force download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($download['file_path']) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clear output buffer
ob_clean();
flush();

// Read and output file
readfile($filePath);
exit;
?>
