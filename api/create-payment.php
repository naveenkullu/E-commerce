<?php
require_once '../config/config.php';
require_once '../config/payment.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Please login to continue'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$orderId = $data['order_id'] ?? 0;

if (empty($orderId)) {
    jsonResponse(['success' => false, 'message' => 'Order ID is required']);
}

// Fetch order details
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, getUserId()]);
$order = $stmt->fetch();

if (!$order) {
    jsonResponse(['success' => false, 'message' => 'Order not found']);
}

$gateway = getActivePaymentGateway();

try {
    switch ($gateway) {
        case 'razorpay':
            // Create Razorpay order
            $amount = formatPaymentAmount($order['final_amount'], 'razorpay');
            
            $orderData = [
                'receipt' => $order['order_number'],
                'amount' => $amount,
                'currency' => getPaymentCurrency(),
                'notes' => [
                    'order_id' => $order['id'],
                    'user_id' => getUserId()
                ]
            ];
            
            // Make API call to Razorpay
            $ch = curl_init('https://api.razorpay.com/v1/orders');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($orderData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode(RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET)
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new Exception('Failed to create Razorpay order');
            }
            
            $razorpayOrder = json_decode($response, true);
            
            // Update order with payment details
            $stmt = $db->prepare("UPDATE orders SET payment_id = ? WHERE id = ?");
            $stmt->execute([$razorpayOrder['id'], $orderId]);
            
            jsonResponse([
                'success' => true,
                'gateway' => 'razorpay',
                'order_id' => $razorpayOrder['id'],
                'amount' => $amount,
                'currency' => getPaymentCurrency(),
                'key' => RAZORPAY_KEY_ID,
                'name' => getSetting('site_name'),
                'description' => 'Order #' . $order['order_number'],
                'prefill' => [
                    'name' => $_SESSION['name'],
                    'email' => $_SESSION['email']
                ]
            ]);
            break;
            
        case 'paypal':
            jsonResponse([
                'success' => true,
                'gateway' => 'paypal',
                'message' => 'PayPal integration coming soon'
            ]);
            break;
            
        case 'stripe':
            jsonResponse([
                'success' => true,
                'gateway' => 'stripe',
                'message' => 'Stripe integration coming soon'
            ]);
            break;
            
        default:
            jsonResponse([
                'success' => true,
                'gateway' => 'manual',
                'message' => 'Manual payment - Please contact admin'
            ]);
    }
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
?>
