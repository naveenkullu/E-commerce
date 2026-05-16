<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Manage Users';

// Fetch users
$searchQuery = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$sql = "SELECT u.*, 
        COUNT(DISTINCT o.id) as order_count,
        SUM(o.final_amount) as total_spent
        FROM users u
        LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'completed'
        WHERE 1=1";

$params = [];

if ($searchQuery) {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if ($roleFilter) {
    $sql .= " AND u.role = ?";
    $params[] = $roleFilter;
}

$sql .= " GROUP BY u.id ORDER BY u.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-users text-primary"></i> Manage Users
        </h1>
        <button class="btn btn-outline-primary" onclick="exportData('users', 'csv')">
            <i class="fas fa-download me-2"></i> Export Users
        </button>
    </div>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name or email..." 
                           value="<?php echo htmlspecialchars($searchQuery); ?>">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select" onchange="this.form.submit()">
                        <option value="">All Roles</option>
                        <option value="user" <?php echo $roleFilter === 'user' ? 'selected' : ''; ?>>Users</option>
                        <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admins</option>
                        <option value="super_admin" <?php echo $roleFilter === 'super_admin' ? 'selected' : ''; ?>>Super Admins</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Users Table -->
    <div class="card shadow-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No users found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&size=40&background=random" 
                                             alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                             class="rounded-circle me-2">
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $user['role'] === 'super_admin' ? 'danger' : 
                                            ($user['role'] === 'admin' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $user['order_count']; ?></td>
                                <td>
                                    <strong class="text-success">
                                        <?php echo formatPrice($user['total_spent'] ?? 0); ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="user-detail.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($user['role'] === 'user'): ?>
                                        <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" 
                                                class="btn btn-sm btn-outline-<?php echo $user['status'] === 'active' ? 'danger' : 'success'; ?>" 
                                                title="<?php echo $user['status'] === 'active' ? 'Block' : 'Unblock'; ?> User">
                                            <i class="fas fa-<?php echo $user['status'] === 'active' ? 'ban' : 'check'; ?>"></i>
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
    <?php if (!empty($users)): ?>
    <div class="row g-4 mt-4">
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?php echo count($users); ?></h3>
                    <p class="text-muted mb-0">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?php 
                        $activeUsers = count(array_filter($users, fn($u) => $u['status'] === 'active'));
                        echo $activeUsers;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Active Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-warning">
                        <?php 
                        $admins = count(array_filter($users, fn($u) => in_array($u['role'], ['admin', 'super_admin'])));
                        echo $admins;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Admins</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        <?php 
                        $totalOrders = array_sum(array_column($users, 'order_count'));
                        echo $totalOrders;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Total Orders</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'blocked' : 'active';
    const action = newStatus === 'blocked' ? 'block' : 'unblock';
    
    if (!confirm(`Are you sure you want to ${action} this user?`)) {
        return;
    }
    
    fetch('api/toggle-user-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            user_id: userId, 
            status: newStatus 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', `User ${action}ed successfully`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error', data.message || 'Failed to update user status', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred', 'danger');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
