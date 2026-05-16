<?php
require_once 'config/config.php';
$pageTitle = 'Products';

// Get filters
$categorySlug = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'latest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';

// Build query
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
        (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";

$params = [];

if ($categorySlug) {
    $sql .= " AND c.slug = ?";
    $params[] = $categorySlug;
}

if ($searchQuery) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

if ($minPrice) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}

if ($maxPrice) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

// Sorting
switch ($sortBy) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'popular':
        $sql .= " ORDER BY p.views DESC, p.downloads DESC";
        break;
    case 'latest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for filter
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

include 'includes/header.php';
?>

<div class="container my-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold">
                <i class="fas fa-box text-primary"></i> 
                <?php echo $categorySlug ? 'Products in ' . ucfirst(str_replace('-', ' ', $categorySlug)) : 'All Products'; ?>
            </h1>
            <p class="text-muted">Browse our collection of premium digital products</p>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="filter-section">
        <form method="GET" action="" id="filterForm">
            <div class="row g-3 align-items-end">
                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label">Search Products</label>
                    <div class="search-bar">
                        <input type="text" name="search" class="form-control" placeholder="Search..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['slug']; ?>" <?php echo $categorySlug === $cat['slug'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div class="col-md-2">
                    <label class="form-label">Min Price</label>
                    <input type="number" name="min_price" class="form-control" placeholder="$0" 
                           value="<?php echo htmlspecialchars($minPrice); ?>" min="0" step="0.01">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Max Price</label>
                    <input type="number" name="max_price" class="form-control" placeholder="$999" 
                           value="<?php echo htmlspecialchars($maxPrice); ?>" min="0" step="0.01">
                </div>
                
                <!-- Sort By -->
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-3">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-select" onchange="this.form.submit()">
                        <option value="latest" <?php echo $sortBy === 'latest' ? 'selected' : ''; ?>>Latest</option>
                        <option value="popular" <?php echo $sortBy === 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>
                
                <?php if ($categorySlug || $searchQuery || $minPrice || $maxPrice): ?>
                <div class="col-md-9 d-flex align-items-end">
                    <a href="products.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-2"></i> Clear Filters
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Products Grid -->
    <div class="row g-4 mt-2">
        <?php if (empty($products)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>No Products Found</h4>
                <p>Try adjusting your filters or search terms</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-redo me-2"></i> View All Products
                </a>
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card product-card">
                    <img src="<?php echo $product['image'] ? BASE_URL . 'uploads/screenshots/' . $product['image'] : 'https://via.placeholder.com/400x300?text=No+Image'; ?>" 
                         class="product-card-img" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>">
                    <div class="product-card-body">
                        <?php if ($product['category_name']): ?>
                        <span class="category-badge bg-primary text-white">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </span>
                        <?php endif; ?>
                        <h5 class="product-card-title">
                            <?php echo htmlspecialchars($product['title']); ?>
                        </h5>
                        <p class="product-card-text">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="product-card-price">
                                <?php echo formatPrice($product['price']); ?>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-download me-1"></i> <?php echo $product['downloads']; ?>
                            </div>
                        </div>
                        <div class="product-card-footer">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <?php if (isLoggedIn()): ?>
                            <button class="btn btn-primary btn-sm flex-fill add-to-cart-btn" 
                                    data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                            <?php else: ?>
                            <a href="login.php" class="btn btn-primary btn-sm flex-fill">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Results Info -->
    <?php if (!empty($products)): ?>
    <div class="row mt-4">
        <div class="col-12 text-center">
            <p class="text-muted">
                Showing <?php echo count($products); ?> product(s)
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
