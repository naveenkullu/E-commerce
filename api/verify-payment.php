<?php
require_once '../config/config.php';
require_once '../config/payment.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$gateway = $data['gateway'] ?? '';
$orderId = $data['order_id'] ?? 0;

if (empty($gateway) || empty($orderId)) {
    jsonResponse(['success' => false, 'message' => 'Missing required parameters']);
}

try {
    $db->beginTransaction();
    
    // Fetch order
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, getUserId()]);
    $order = $stmt->fetch();
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    switch ($gateway) {
        case 'razorpay':
            $razorpayPaymentId = $data['razorpay_payment_id'] ?? '';
            $razorpayOrderId = $data['razorpay_order_id'] ?? '';
            $razorpaySignature = $data['razorpay_signature'] ?? '';
            
            if (empty($razorpayPaymentId) || empty($razorpayOrderId) || empty($razorpaySignature)) {
                throw new Exception('Missing Razorpay payment details');
            }
            
            // Verify signature
            $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);
            
            if ($expectedSignature !== $razorpaySignature) {
                throw new Exception('Invalid payment signature');
            }
            
            // Update order status
            $stmt = $db->prepare("
                UPDATE orders 
                SET payment_status = 'completed', 
                    payment_id = ?,
                    status = 'completed',
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$razorpayPaymentId, $orderId]);
            
            // Clear cart
            unset($_SESSION['cart']);
            unset($_SESSION['discount_amount']);
            unset($_SESSION['coupon_code']);
            
            $db->commit();
            
            jsonResponse([
                'success' => true,
                'message' => 'Payment verified successfully!',
                'order_number' => $order['order_number'],
                'redirect' => BASE_URL . 'order-success.php?order=' . $order['order_number']
            ]);
            break;
            
        case 'paypal':
        case 'stripe':
            // TODO: Implement PayPal and Stripe verification
            throw new Exception('Payment gateway not yet implemented');
            break;
            
        default:
            throw new Exception('Invalid payment gateway');
    }
    
} catch (Exception $e) {
    $db->rollBack();
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
