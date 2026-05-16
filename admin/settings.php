<?php
require_once '../config/config.php';

if (!isSuperAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Settings';
$success = '';
$error = '';

// Fetch all settings
$stmt = $db->query("SELECT setting_key, setting_value FROM settings ORDER BY setting_key");
$settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'submit') {
                $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([sanitize($value), $key]);
            }
        }
        
        $db->commit();
        $success = 'Settings updated successfully!';
        
        // Refresh settings
        $stmt = $db->query("SELECT setting_key, setting_value FROM settings ORDER BY setting_key");
        $settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Failed to update settings: ' . $e->getMessage();
    }
}

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-cog text-primary"></i> System Settings
    </h1>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="row g-4">
            <!-- General Settings -->
            <div class="col-lg-6">
                <div class="admin-form-section">
                    <h5><i class="fas fa-globe me-2"></i> General Settings</h5>
                    
                    <div class="form-outline mb-4">
                        <input type="text" id="site_name" name="site_name" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['site_name'] ?? ''); ?>">
                        <label class="form-label" for="site_name">Site Name</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <input type="email" id="site_email" name="site_email" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['site_email'] ?? ''); ?>">
                        <label class="form-label" for="site_email">Site Email</label>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="text" id="currency" name="currency" class="form-control" 
                                       value="<?php echo htmlspecialchars($settingsData['currency'] ?? 'USD'); ?>">
                                <label class="form-label" for="currency">Currency Code</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="text" id="currency_symbol" name="currency_symbol" class="form-control" 
                                       value="<?php echo htmlspecialchars($settingsData['currency_symbol'] ?? '$'); ?>">
                                <label class="form-label" for="currency_symbol">Currency Symbol</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tax Settings -->
                <div class="admin-form-section">
                    <h5><i class="fas fa-percent me-2"></i> Tax Settings</h5>
                    
                    <div class="form-outline mb-4">
                        <input type="number" id="tax_percentage" name="tax_percentage" class="form-control" 
                               step="0.01" min="0" max="100"
                               value="<?php echo htmlspecialchars($settingsData['tax_percentage'] ?? '0'); ?>">
                        <label class="form-label" for="tax_percentage">Tax Percentage (%)</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <input type="number" id="gst_percentage" name="gst_percentage" class="form-control" 
                               step="0.01" min="0" max="100"
                               value="<?php echo htmlspecialchars($settingsData['gst_percentage'] ?? '0'); ?>">
                        <label class="form-label" for="gst_percentage">GST Percentage (%)</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <input type="number" id="vat_percentage" name="vat_percentage" class="form-control" 
                               step="0.01" min="0" max="100"
                               value="<?php echo htmlspecialchars($settingsData['vat_percentage'] ?? '0'); ?>">
                        <label class="form-label" for="vat_percentage">VAT Percentage (%)</label>
                    </div>
                </div>
                
                <!-- Download Settings -->
                <div class="admin-form-section">
                    <h5><i class="fas fa-download me-2"></i> Download Settings</h5>
                    
                    <div class="form-outline mb-4">
                        <input type="number" id="download_expiry_days" name="download_expiry_days" class="form-control" 
                               min="1" value="<?php echo htmlspecialchars($settingsData['download_expiry_days'] ?? '30'); ?>">
                        <label class="form-label" for="download_expiry_days">Download Expiry (Days)</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <input type="number" id="max_downloads_per_product" name="max_downloads_per_product" class="form-control" 
                               min="1" value="<?php echo htmlspecialchars($settingsData['max_downloads_per_product'] ?? '5'); ?>">
                        <label class="form-label" for="max_downloads_per_product">Max Downloads Per Product</label>
                    </div>
                </div>
            </div>
            
            <!-- Payment Gateway Settings -->
            <div class="col-lg-6">
                <div class="admin-form-section">
                    <h5><i class="fas fa-credit-card me-2"></i> Payment Gateway</h5>
                    
                    <div class="mb-4">
                        <label class="form-label">Active Payment Gateway</label>
                        <select name="payment_gateway" class="form-select">
                            <option value="razorpay" <?php echo ($settingsData['payment_gateway'] ?? '') === 'razorpay' ? 'selected' : ''; ?>>Razorpay</option>
                            <option value="stripe" <?php echo ($settingsData['payment_gateway'] ?? '') === 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                            <option value="paypal" <?php echo ($settingsData['payment_gateway'] ?? '') === 'paypal' ? 'selected' : ''; ?>>PayPal</option>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Razorpay Settings</h6>
                    <div class="form-outline mb-3">
                        <input type="text" id="razorpay_key_id" name="razorpay_key_id" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['razorpay_key_id'] ?? ''); ?>">
                        <label class="form-label" for="razorpay_key_id">Razorpay Key ID</label>
                    </div>
                    <div class="form-outline mb-4">
                        <input type="text" id="razorpay_key_secret" name="razorpay_key_secret" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['razorpay_key_secret'] ?? ''); ?>">
                        <label class="form-label" for="razorpay_key_secret">Razorpay Key Secret</label>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Stripe Settings</h6>
                    <div class="form-outline mb-3">
                        <input type="text" id="stripe_public_key" name="stripe_public_key" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['stripe_public_key'] ?? ''); ?>">
                        <label class="form-label" for="stripe_public_key">Stripe Public Key</label>
                    </div>
                    <div class="form-outline mb-4">
                        <input type="text" id="stripe_secret_key" name="stripe_secret_key" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['stripe_secret_key'] ?? ''); ?>">
                        <label class="form-label" for="stripe_secret_key">Stripe Secret Key</label>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">PayPal Settings</h6>
                    <div class="form-outline mb-3">
                        <input type="text" id="paypal_client_id" name="paypal_client_id" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['paypal_client_id'] ?? ''); ?>">
                        <label class="form-label" for="paypal_client_id">PayPal Client ID</label>
                    </div>
                    <div class="form-outline mb-3">
                        <input type="text" id="paypal_secret" name="paypal_secret" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['paypal_secret'] ?? ''); ?>">
                        <label class="form-label" for="paypal_secret">PayPal Secret</label>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">PayPal Mode</label>
                        <select name="paypal_mode" class="form-select">
                            <option value="sandbox" <?php echo ($settingsData['paypal_mode'] ?? '') === 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
                            <option value="live" <?php echo ($settingsData['paypal_mode'] ?? '') === 'live' ? 'selected' : ''; ?>>Live</option>
                        </select>
                    </div>
                </div>
                
                <!-- Email Settings -->
                <div class="admin-form-section">
                    <h5><i class="fas fa-envelope me-2"></i> Email Settings (SMTP)</h5>
                    
                    <div class="form-outline mb-3">
                        <input type="text" id="smtp_host" name="smtp_host" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['smtp_host'] ?? ''); ?>">
                        <label class="form-label" for="smtp_host">SMTP Host</label>
                    </div>
                    
                    <div class="form-outline mb-3">
                        <input type="number" id="smtp_port" name="smtp_port" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['smtp_port'] ?? '587'); ?>">
                        <label class="form-label" for="smtp_port">SMTP Port</label>
                    </div>
                    
                    <div class="form-outline mb-3">
                        <input type="text" id="smtp_username" name="smtp_username" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['smtp_username'] ?? ''); ?>">
                        <label class="form-label" for="smtp_username">SMTP Username</label>
                    </div>
                    
                    <div class="form-outline mb-3">
                        <input type="password" id="smtp_password" name="smtp_password" class="form-control" 
                               value="<?php echo htmlspecialchars($settingsData['smtp_password'] ?? ''); ?>">
                        <label class="form-label" for="smtp_password">SMTP Password</label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">SMTP Encryption</label>
                        <select name="smtp_encryption" class="form-select">
                            <option value="tls" <?php echo ($settingsData['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo ($settingsData['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="order_confirmation_email" 
                               name="order_confirmation_email" value="1" 
                               <?php echo ($settingsData['order_confirmation_email'] ?? '1') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="order_confirmation_email">
                            Send Order Confirmation Email
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="payment_failed_email" 
                               name="payment_failed_email" value="1" 
                               <?php echo ($settingsData['payment_failed_email'] ?? '1') == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="payment_failed_email">
                            Send Payment Failed Email
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Save Button -->
        <div class="text-center mt-4">
            <button type="submit" name="submit" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-save me-2"></i> Save All Settings
            </button>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
