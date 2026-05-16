<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Manage Products';

// Fetch products
$stmt = $db->query("
    SELECT p.*, c.name as category_name,
           (SELECT COUNT(*) FROM order_items WHERE product_id = p.id) as sales_count
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-box text-primary"></i> Manage Products
        </h1>
        <a href="product-add.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Add New Product
        </a>
    </div>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="row g-3">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" 
                       placeholder="Search products..." 
                       onkeyup="filterTable('searchInput', 'productsTable')">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter" onchange="filterByStatus()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100" onclick="exportData('products', 'csv')">
                    <i class="fas fa-download me-2"></i> Export CSV
                </button>
            </div>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="card shadow-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="productsTable">
                    <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" onchange="toggleSelectAll(this)"></th>
                            <th>ID</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Sales</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No products found. Add your first product!</p>
                                <a href="product-add.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Add Product
                                </a>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><input type="checkbox" class="item-checkbox" value="<?php echo $product['id']; ?>"></td>
                                <td><strong>#<?php echo $product['id']; ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($product['title']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($product['category_name']): ?>
                                    <span class="badge bg-info"><?php echo htmlspecialchars($product['category_name']); ?></span>
                                    <?php else: ?>
                                    <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong class="text-primary"><?php echo formatPrice($product['price']); ?></strong></td>
                                <td><?php echo $product['sales_count']; ?></td>
                                <td><?php echo $product['views']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($product['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="toggleStatus(<?php echo $product['id']; ?>, 'product', '<?php echo $product['status']; ?>', this)" 
                                                class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                            <i class="fas fa-toggle-on"></i>
                                        </button>
                                        <button onclick="deleteItem(<?php echo $product['id']; ?>, 'product', this)" 
                                                class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
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
    
    <!-- Bulk Actions -->
    <?php if (!empty($products)): ?>
    <div class="mt-3">
        <div class="btn-group">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-mdb-toggle="dropdown">
                Bulk Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="handleBulkAction('activate', 'product')">Activate</a></li>
                <li><a class="dropdown-item" href="#" onclick="handleBulkAction('deactivate', 'product')">Deactivate</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" onclick="handleBulkAction('delete', 'product')">Delete</a></li>
            </ul>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function filterByStatus() {
    const filter = document.getElementById('statusFilter').value.toLowerCase();
    const table = document.getElementById('productsTable');
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        if (filter === '') {
            tr[i].style.display = '';
        } else {
            const statusCell = tr[i].getElementsByTagName('td')[7];
            if (statusCell) {
                const status = statusCell.textContent.toLowerCase();
                tr[i].style.display = status.includes(filter) ? '' : 'none';
            }
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>
