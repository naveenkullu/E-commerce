<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Manage Orders';

// Fetch orders with filters
$statusFilter = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

$sql = "SELECT o.*, u.name as user_name, u.email as user_email,
        COUNT(oi.id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE 1=1";

$params = [];

if ($statusFilter) {
    $sql .= " AND o.payment_status = ?";
    $params[] = $statusFilter;
}

if ($searchQuery) {
    $sql .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

$sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-shopping-cart text-primary"></i> Manage Orders
        </h1>
        <button class="btn btn-outline-primary" onclick="exportData('orders', 'csv')">
            <i class="fas fa-download me-2"></i> Export Orders
        </button>
    </div>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by order number, customer name or email..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="orders.php" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-2"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Orders Table -->
    <div class="card shadow-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No orders found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong class="text-primary"><?php echo $order['order_number']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($order['user_name']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                                </td>
                                <td><?php echo $order['item_count']; ?> item(s)</td>
                                <td><strong class="text-success"><?php echo formatPrice($order['final_amount']); ?></strong></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo strtoupper($order['payment_gateway']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $order['payment_status'] === 'completed' ? 'success' : 
                                            ($order['payment_status'] === 'pending' ? 'warning' : 
                                            ($order['payment_status'] === 'refunded' ? 'info' : 'danger')); 
                                    ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDateTime($order['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($order['payment_status'] === 'pending'): ?>
                                        <button onclick="approvePayment(<?php echo $order['id']; ?>, 'approve')" 
                                                class="btn btn-sm btn-outline-success" title="Approve Payment">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button onclick="approvePayment(<?php echo $order['id']; ?>, 'reject')" 
                                                class="btn btn-sm btn-outline-danger" title="Reject Payment">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <?php endif; ?>
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
    
    <!-- Summary Cards -->
    <?php if (!empty($orders)): ?>
    <div class="row g-4 mt-4">
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?php echo count($orders); ?></h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?php 
                        $totalRevenue = array_sum(array_column($orders, 'final_amount'));
                        echo formatPrice($totalRevenue);
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-warning">
                        <?php 
                        $pendingCount = count(array_filter($orders, fn($o) => $o['payment_status'] === 'pending'));
                        echo $pendingCount;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Pending Orders</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        <?php 
                        $completedCount = count(array_filter($orders, fn($o) => $o['payment_status'] === 'completed'));
                        echo $completedCount;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Completed Orders</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
