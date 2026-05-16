<?php
// Payment Gateway Configuration

// Razorpay Configuration
define('RAZORPAY_KEY_ID', getSetting('razorpay_key_id') ?? 'rzp_test_xxxxxxxxxx');
define('RAZORPAY_KEY_SECRET', getSetting('razorpay_key_secret') ?? 'xxxxxxxxxxxxxxxxxx');
define('RAZORPAY_ENABLED', getSetting('razorpay_enabled') ?? false);

// PayPal Configuration
define('PAYPAL_CLIENT_ID', getSetting('paypal_client_id') ?? '');
define('PAYPAL_CLIENT_SECRET', getSetting('paypal_client_secret') ?? '');
define('PAYPAL_MODE', getSetting('paypal_mode') ?? 'sandbox'); // sandbox or live
define('PAYPAL_ENABLED', getSetting('paypal_enabled') ?? false);

// Stripe Configuration
define('STRIPE_PUBLISHABLE_KEY', getSetting('stripe_publishable_key') ?? '');
define('STRIPE_SECRET_KEY', getSetting('stripe_secret_key') ?? '');
define('STRIPE_ENABLED', getSetting('stripe_enabled') ?? false);

// Get active payment gateway
function getActivePaymentGateway() {
    if (RAZORPAY_ENABLED) return 'razorpay';
    if (PAYPAL_ENABLED) return 'paypal';
    if (STRIPE_ENABLED) return 'stripe';
    return 'cod'; // Cash on delivery / Manual
}

// Format amount for payment gateway (convert to smallest currency unit)
function formatPaymentAmount($amount, $gateway = 'razorpay') {
    switch ($gateway) {
        case 'razorpay':
            // Razorpay accepts amount in paise (multiply by 100)
            return intval($amount * 100);
        case 'stripe':
            // Stripe accepts amount in cents (multiply by 100)
            return intval($amount * 100);
        case 'paypal':
            // PayPal accepts amount as is
            return number_format($amount, 2, '.', '');
        default:
            return $amount;
    }
}

// Get currency code
function getPaymentCurrency() {
    return getSetting('currency') ?? 'INR';
}
?>
