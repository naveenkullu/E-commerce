<?php
require_once 'config/config.php';
$pageTitle = 'Home';
include 'includes/header.php';

// Fetch stats
$stats = [
    'total_products' => $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
    'total_users' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'total_downloads' => $db->query("SELECT SUM(downloads) FROM products")->fetchColumn() ?? 0,
    'happy_customers' => $db->query("SELECT COUNT(DISTINCT user_id) FROM orders WHERE payment_status = 'completed'")->fetchColumn()
];

// Fetch featured products
$featuredProducts = $db->query("
    SELECT p.*, c.name as category_name,
           (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY p.views DESC, p.created_at DESC
    LIMIT 8
")->fetchAll();

// Fetch trending products (most viewed in last 7 days)
$trendingProducts = $db->query("
    SELECT p.*, c.name as category_name,
           (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active' AND p.created_at >= (CURRENT_TIMESTAMP - INTERVAL '7 days')
    ORDER BY p.views DESC
    LIMIT 4
")->fetchAll();

// Fetch categories
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

// Fetch testimonials (mock data for now)
$testimonials = [
    [
        'name' => 'John Doe',
        'text' => 'Amazing digital products! The quality is outstanding and the download process is seamless.',
        'rating' => 5,
        'avatar' => 'https://via.placeholder.com/80'
    ],
    [
        'name' => 'Jane Smith',
        'text' => 'Great marketplace with excellent customer support. Highly recommended!',
        'rating' => 5,
        'avatar' => 'https://via.placeholder.com/80'
    ],
    [
        'name' => 'Mike Johnson',
        'text' => 'Found exactly what I was looking for. Fast delivery and great prices.',
        'rating' => 4,
        'avatar' => 'https://via.placeholder.com/80'
    ]
];
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 text-center text-lg-start mb-4 mb-lg-0">
                <h1 class="display-3 fw-bold mb-3 animate-fade-in">Premium Digital Products</h1>
                <p class="lead mb-4 animate-fade-in-delay">Discover thousands of high-quality digital products for your business and personal projects</p>
                <div class="d-flex gap-3 justify-content-center justify-content-lg-start flex-wrap animate-fade-in-delay-2">
                    <a href="products.php" class="btn btn-light btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i> Explore Products
                    </a>
                    <?php if (!isLoggedIn()): ?>
                    <a href="signup.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i> Get Started Free
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Stats Counter -->
                <div class="row mt-5 text-center text-lg-start">
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_products']); ?>+</h3>
                            <p class="mb-0 small">Products</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_users']); ?>+</h3>
                            <p class="mb-0 small">Users</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="fw-bold mb-0"><?php echo number_format($stats['total_downloads']); ?>+</h3>
                            <p class="mb-0 small">Downloads</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-item">
                            <h3 class="fw-bold mb-0"><?php echo number_format($stats['happy_customers']); ?>+</h3>
                            <p class="mb-0 small">Happy Customers</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-image-container">
                    <img src="https://img.freepik.com/free-vector/online-shopping-concept-landing-page_52683-20879.jpg" alt="Digital Products" class="img-fluid rounded-custom animate-float">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="container my-5">
    <h2 class="section-title">Browse Categories</h2>
    <div class="row g-4">
        <?php 
        // Category icons and colors mapping
        $categoryStyles = [
            'wordpress-themes' => ['icon' => 'fab fa-wordpress', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
            'mobile-apps' => ['icon' => 'fas fa-mobile-alt', 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
            'graphics-design' => ['icon' => 'fas fa-palette', 'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'],
            'ebooks' => ['icon' => 'fas fa-book', 'gradient' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'],
            'software-tools' => ['icon' => 'fas fa-tools', 'gradient' => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)']
        ];
        
        foreach ($categories as $category): 
            $style = $categoryStyles[$category['slug']] ?? ['icon' => 'fas fa-folder', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'];
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <a href="products.php?category=<?php echo $category['slug']; ?>" class="text-decoration-none">
                <div class="category-card">
                    <div class="category-icon" style="background: <?php echo $style['gradient']; ?>">
                        <i class="<?php echo $style['icon']; ?>"></i>
                    </div>
                    <h5 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                    <p class="category-desc"><?php echo htmlspecialchars($category['description']); ?></p>
                    <div class="category-arrow">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Trending Products Section -->
<?php if (!empty($trendingProducts)): ?>
<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title mb-0">
            <i class="fas fa-fire text-danger me-2"></i>Trending This Week
        </h2>
        <a href="products.php?sort=trending" class="btn btn-outline-primary btn-sm">View All</a>
    </div>
    <div class="row g-4">
        <?php foreach ($trendingProducts as $product): ?>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="card product-card trending-badge-container">
                <span class="trending-badge"><i class="fas fa-fire"></i> Trending</span>
                <img src="<?php echo $product['image'] ? BASE_URL . 'uploads/screenshots/' . $product['image'] : 'https://via.placeholder.com/400x300?text=No+Image'; ?>" 
                     class="product-card-img" 
                     alt="<?php echo htmlspecialchars($product['title']); ?>">
                <div class="product-card-body">
                    <?php if ($product['category_name']): ?>
                    <span class="category-badge bg-danger text-white">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                    <?php endif; ?>
                    <h5 class="product-card-title">
                        <?php echo htmlspecialchars($product['title']); ?>
                    </h5>
                    <p class="product-card-text">
                        <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?>
                    </p>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="product-card-price">
                            <?php echo formatPrice($product['price']); ?>
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-eye"></i> <?php echo number_format($product['views']); ?>
                        </div>
                    </div>
                    <div class="product-card-footer">
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm flex-fill">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-primary btn-sm flex-fill add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-cart-plus"></i> Add
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products Section -->
<section class="container my-5">
    <h2 class="section-title">Featured Products</h2>
    <div class="row g-4">
        <?php if (empty($featuredProducts)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i>
                No products available yet. Check back soon!
            </div>
        </div>
        <?php else: ?>
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-12 col-md-6 col-lg-3">
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
                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                        </p>
                        <div class="product-card-price">
                            <?php echo formatPrice($product['price']); ?>
                        </div>
                        <div class="product-card-footer">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <?php if (isLoggedIn()): ?>
                            <button class="btn btn-primary btn-sm flex-fill add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus"></i> Add
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($featuredProducts)): ?>
    <div class="text-center mt-4">
        <a href="products.php" class="btn btn-primary btn-lg">
            <i class="fas fa-th me-2"></i> View All Products
        </a>
    </div>
    <?php endif; ?>
</section>

<!-- Features Section -->
<section class="container my-5">
    <h2 class="section-title">Why Choose Us</h2>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow-custom">
                <div class="card-body">
                    <i class="fas fa-download fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Instant Download</h5>
                    <p class="card-text text-muted">Get your products immediately after purchase</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow-custom">
                <div class="card-body">
                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Secure Payment</h5>
                    <p class="card-text text-muted">Multiple secure payment options available</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow-custom">
                <div class="card-body">
                    <i class="fas fa-headset fa-3x text-info mb-3"></i>
                    <h5 class="card-title">24/7 Support</h5>
                    <p class="card-text text-muted">Our team is always here to help you</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow-custom">
                <div class="card-body">
                    <i class="fas fa-sync-alt fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Money Back</h5>
                    <p class="card-text text-muted">30-day money back guarantee</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="container my-5">
    <h2 class="section-title">What Our Customers Say</h2>
    <div class="row g-4">
        <?php foreach ($testimonials as $testimonial): ?>
        <div class="col-md-6 col-lg-4">
            <div class="testimonial-card">
                <img src="<?php echo $testimonial['avatar']; ?>" alt="<?php echo $testimonial['name']; ?>" class="testimonial-avatar">
                <div class="mb-3">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i < $testimonial['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                    <?php endfor; ?>
                </div>
                <p class="testimonial-text">"<?php echo htmlspecialchars($testimonial['text']); ?>"</p>
                <p class="testimonial-author"><?php echo htmlspecialchars($testimonial['name']); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="container my-5">
    <div class="card shadow-custom" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
        <div class="card-body text-center py-5">
            <h2 class="fw-bold mb-3">Ready to Get Started?</h2>
            <p class="lead mb-4">Join thousands of satisfied customers today!</p>
            <?php if (isLoggedIn()): ?>
            <a href="products.php" class="btn btn-light btn-lg">
                <i class="fas fa-shopping-bag me-2"></i> Browse Products
            </a>
            <?php else: ?>
            <a href="signup.php" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i> Create Free Account
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Back to Top Button -->
<button id="backToTop" class="btn btn-primary rounded-circle position-fixed" 
        style="bottom: 80px; right: 20px; width: 50px; height: 50px; display: none; z-index: 999;"
        onclick="scrollToTop()">
    <i class="fas fa-arrow-up"></i>
</button>

<?php include 'includes/footer.php'; ?>
