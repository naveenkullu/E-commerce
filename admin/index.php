<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Admin Dashboard';

// Fetch statistics
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_products' => $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'total_orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_revenue' => $db->query("SELECT SUM(final_amount) FROM orders WHERE payment_status = 'completed'")->fetchColumn() ?? 0,
    'pending_orders' => $db->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'pending'")->fetchColumn(),
    'open_tickets' => $db->query("SELECT COUNT(*) FROM support_tickets WHERE status = 'open'")->fetchColumn()
];

// Recent orders
$recentOrders = $db->query("
    SELECT o.*, u.name as user_name, u.email as user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
")->fetchAll();

// Top products
$topProducts = $db->query("
    SELECT p.*, COUNT(oi.id) as order_count
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    WHERE p.status = 'active'
    GROUP BY p.id
    ORDER BY order_count DESC
    LIMIT 5
")->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <h1 class="fw-bold mb-4">
        <i class="fas fa-chart-line text-primary"></i> Dashboard Overview
    </h1>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-custom h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Revenue</p>
                            <h3 class="fw-bold text-success mb-0"><?php echo formatPrice($stats['total_revenue']); ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-dollar-sign fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-custom h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Orders</p>
                            <h3 class="fw-bold text-primary mb-0"><?php echo $stats['total_orders']; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-custom h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Products</p>
                            <h3 class="fw-bold text-info mb-0"><?php echo $stats['total_products']; ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-box fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-custom h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Users</p>
                            <h3 class="fw-bold text-warning mb-0"><?php echo $stats['total_users']; ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Pending Orders</p>
                            <h4 class="fw-bold mb-0"><?php echo $stats['pending_orders']; ?></h4>
                        </div>
                        <a href="orders.php?status=pending" class="btn btn-outline-primary btn-sm">
                            View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Open Support Tickets</p>
                            <h4 class="fw-bold mb-0"><?php echo $stats['open_tickets']; ?></h4>
                        </div>
                        <a href="support.php?status=open" class="btn btn-outline-primary btn-sm">
                            View All
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card shadow-custom">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Recent Orders</h5>
                        <a href="orders.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No orders yet</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['user_name']); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($order['user_email']); ?></small>
                                        </td>
                                        <td><strong class="text-primary"><?php echo formatPrice($order['final_amount']); ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?php echo $order['payment_status'] === 'completed' ? 'success' : ($order['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($order['created_at']); ?></td>
                                        <td>
                                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
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
        
        <!-- Top Products -->
        <div class="col-lg-4">
            <div class="card shadow-custom">
                <div class="card-body">
                    <h5 class="card-title mb-4">Top Selling Products</h5>
                    
                    <?php if (empty($topProducts)): ?>
                    <p class="text-muted text-center">No products yet</p>
                    <?php else: ?>
                        <?php foreach ($topProducts as $product): ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($product['title']); ?></h6>
                                <small class="text-muted"><?php echo $product['order_count']; ?> sales</small>
                            </div>
                            <strong class="text-primary"><?php echo formatPrice($product['price']); ?></strong>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
