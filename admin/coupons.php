<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Manage Coupons';
$success = '';
$error = '';

// Handle coupon creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_coupon'])) {
    $code = strtoupper(sanitize($_POST['code'] ?? ''));
    $type = $_POST['type'] ?? 'flat';
    $value = floatval($_POST['value'] ?? 0);
    $minPurchase = floatval($_POST['min_purchase'] ?? 0);
    $maxDiscount = floatval($_POST['max_discount'] ?? 0);
    $usageLimit = intval($_POST['usage_limit'] ?? 0);
    $expiryDate = $_POST['expiry_date'] ?? null;
    $status = $_POST['status'] ?? 'active';
    
    if (empty($code) || $value <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if code exists
        $stmt = $db->prepare("SELECT id FROM coupons WHERE code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->fetch()) {
            $error = 'Coupon code already exists';
        } else {
            $stmt = $db->prepare("
                INSERT INTO coupons (code, type, value, min_purchase, max_discount, usage_limit, expiry_date, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$code, $type, $value, $minPurchase, $maxDiscount ?: null, $usageLimit ?: null, $expiryDate ?: null, $status])) {
                $success = 'Coupon created successfully!';
            } else {
                $error = 'Failed to create coupon';
            }
        }
    }
}

// Fetch coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-tag text-primary"></i> Manage Coupons
        </h1>
        <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#createCouponModal">
            <i class="fas fa-plus me-2"></i> Create Coupon
        </button>
    </div>
    
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
    
    <!-- Coupons Table -->
    <div class="card shadow-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Code</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Purchase</th>
                            <th>Max Discount</th>
                            <th>Usage</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-tag fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No coupons found. Create your first coupon!</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td><strong class="text-primary"><?php echo $coupon['code']; ?></strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo ucfirst($coupon['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($coupon['type'] === 'flat') {
                                        echo formatPrice($coupon['value']);
                                    } else {
                                        echo $coupon['value'] . '%';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $coupon['min_purchase'] > 0 ? formatPrice($coupon['min_purchase']) : '-'; ?></td>
                                <td><?php echo $coupon['max_discount'] ? formatPrice($coupon['max_discount']) : '-'; ?></td>
                                <td>
                                    <?php 
                                    if ($coupon['usage_limit']) {
                                        echo $coupon['used_count'] . ' / ' . $coupon['usage_limit'];
                                    } else {
                                        echo $coupon['used_count'] . ' / ∞';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($coupon['expiry_date']) {
                                        echo formatDate($coupon['expiry_date']);
                                        if (strtotime($coupon['expiry_date']) < time()) {
                                            echo '<br><span class="badge bg-danger">Expired</span>';
                                        }
                                    } else {
                                        echo 'No Expiry';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $coupon['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($coupon['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="toggleStatus(<?php echo $coupon['id']; ?>, 'coupon', '<?php echo $coupon['status']; ?>', this)" 
                                                class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteItem(<?php echo $coupon['id']; ?>, 'coupon', this)" 
                                                class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Coupon Modal -->
<div class="modal fade" id="createCouponModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i> Create New Coupon
                </h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="text" id="code" name="code" class="form-control" required style="text-transform: uppercase;">
                                <label class="form-label" for="code">Coupon Code *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select name="type" id="type" class="form-select" required>
                                <option value="flat">Flat Discount</option>
                                <option value="percentage">Percentage Discount</option>
                            </select>
                            <label class="form-label" for="type">Discount Type *</label>
                        </div>
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="number" id="value" name="value" class="form-control" step="0.01" min="0" required>
                                <label class="form-label" for="value">Discount Value *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="number" id="min_purchase" name="min_purchase" class="form-control" step="0.01" min="0">
                                <label class="form-label" for="min_purchase">Minimum Purchase</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="number" id="max_discount" name="max_discount" class="form-control" step="0.01" min="0">
                                <label class="form-label" for="max_discount">Max Discount (for %)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="number" id="usage_limit" name="usage_limit" class="form-control" min="0">
                                <label class="form-label" for="usage_limit">Usage Limit (0 = unlimited)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="datetime-local" id="expiry_date" name="expiry_date" class="form-control">
                                <label class="form-label" for="expiry_date">Expiry Date (optional)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <label class="form-label">Status</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_coupon" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Create Coupon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
