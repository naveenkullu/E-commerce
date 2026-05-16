<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['order_id'] ?? 0;
$action = $data['action'] ?? ''; // approve or reject

if (empty($orderId) || empty($action)) {
    jsonResponse(['success' => false, 'message' => 'Missing required parameters']);
}

try {
    $db->beginTransaction();
    
    if ($action === 'approve') {
        // Approve payment
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'completed',
                status = 'completed',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        $message = 'Payment approved successfully!';
        
    } elseif ($action === 'reject') {
        // Reject payment
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'failed',
                status = 'cancelled',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        $message = 'Payment rejected!';
        
    } else {
        throw new Exception('Invalid action');
    }
    
    $db->commit();
    
    jsonResponse([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
