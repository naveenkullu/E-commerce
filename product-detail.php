<?php
require_once 'config/config.php';

$productId = $_GET['id'] ?? 0;

// Fetch product details
$stmt = $db->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.status = 'active'
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products.php');
}

// Update view count
$db->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$productId]);

// Fetch screenshots
$screenshots = $db->prepare("SELECT * FROM product_screenshots WHERE product_id = ? ORDER BY display_order");
$screenshots->execute([$productId]);
$images = $screenshots->fetchAll();

// Fetch related products
$relatedProducts = $db->prepare("
    SELECT p.*,
           (SELECT image_path FROM product_screenshots WHERE product_id = p.id ORDER BY display_order LIMIT 1) as image
    FROM products p
    WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
    ORDER BY RAND()
    LIMIT 4
");
$relatedProducts->execute([$product['category_id'], $productId]);
$related = $relatedProducts->fetchAll();

// Fetch reviews
$reviewsStmt = $db->prepare("
    SELECT pr.*, u.name as user_name,
           (SELECT COUNT(*) FROM review_helpful_votes WHERE review_id = pr.id) as helpful_count,
           (SELECT COUNT(*) FROM review_helpful_votes WHERE review_id = pr.id AND user_id = ?) as user_voted
    FROM product_reviews pr
    JOIN users u ON pr.user_id = u.id
    WHERE pr.product_id = ? AND pr.status = 'approved'
    ORDER BY pr.created_at DESC
    LIMIT 10
");
$reviewsStmt->execute([getUserId() ?? 0, $productId]);
$reviews = $reviewsStmt->fetchAll();

// Check if user can review
$canReview = false;
if (isLoggedIn()) {
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.payment_status = 'completed'
    ");
    $stmt->execute([getUserId(), $productId]);
    $hasPurchased = $stmt->fetchColumn() > 0;
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$productId, getUserId()]);
    $hasReviewed = $stmt->fetchColumn() > 0;
    
    $canReview = $hasPurchased && !$hasReviewed;
}

$pageTitle = $product['title'];
include 'includes/header.php';
?>

<div class="container my-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>products.php">Products</a></li>
            <?php if ($product['category_name']): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo BASE_URL; ?>products.php?category=<?php echo $product['category_slug']; ?>">
                    <?php echo htmlspecialchars($product['category_name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
        </ol>
    </nav>
    
    <!-- Product Details -->
    <div class="row g-4">
        <!-- Product Images -->
        <div class="col-lg-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-0">
                    <?php if (!empty($images)): ?>
                    <div id="productCarousel" class="carousel slide" data-mdb-ride="carousel">
                        <div class="carousel-indicators">
                            <?php foreach ($images as $index => $img): ?>
                            <button type="button" data-mdb-target="#productCarousel" 
                                    data-mdb-slide-to="<?php echo $index; ?>" 
                                    <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
                            <?php endforeach; ?>
                        </div>
                        <div class="carousel-inner">
                            <?php foreach ($images as $index => $img): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo BASE_URL . 'uploads/screenshots/' . $img['image_path']; ?>" 
                                     class="d-block w-100" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>"
                                     style="max-height: 500px; object-fit: contain; background: #f8f9fa;">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-mdb-target="#productCarousel" data-mdb-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-mdb-target="#productCarousel" data-mdb-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    </div>
                    <?php else: ?>
                    <img src="https://via.placeholder.com/600x400?text=No+Image" 
                         class="img-fluid" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>">
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="card shadow-custom rounded-custom">
                <div class="card-body p-4">
                    <?php if ($product['category_name']): ?>
                    <span class="badge bg-primary mb-3">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                    <?php endif; ?>
                    
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>
                    
                    <div class="d-flex align-items-center mb-4">
                        <div class="text-warning me-3">
                            <?php 
                            $avgRating = $product['average_rating'] ?? 0;
                            $fullStars = floor($avgRating);
                            $halfStar = ($avgRating - $fullStars) >= 0.5;
                            $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                            
                            for ($i = 0; $i < $fullStars; $i++) echo '<i class="fas fa-star"></i> ';
                            if ($halfStar) echo '<i class="fas fa-star-half-alt"></i> ';
                            for ($i = 0; $i < $emptyStars; $i++) echo '<i class="far fa-star"></i> ';
                            ?>
                            <span class="text-muted ms-2">(<?php echo number_format($avgRating, 1); ?>)</span>
                        </div>
                        <div class="text-muted me-3">
                            <i class="fas fa-comment me-1"></i> <?php echo $product['total_reviews'] ?? 0; ?> reviews
                        </div>
                        <div class="text-muted">
                            <i class="fas fa-download me-1"></i> <?php echo number_format($product['downloads']); ?> downloads
                        </div>
                    </div>
                    
                    <h2 class="text-primary fw-bold mb-4">
                        <?php echo formatPrice($product['price']); ?>
                    </h2>
                    
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Description</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    </div>
                    
                    <?php if ($product['file_size']): ?>
                    <div class="mb-3">
                        <strong>File Size:</strong> <span class="text-muted"><?php echo $product['file_size']; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($product['demo_url']): ?>
                    <div class="mb-4">
                        <a href="<?php echo htmlspecialchars($product['demo_url']); ?>" 
                           target="_blank" 
                           class="btn btn-outline-info">
                            <i class="fas fa-external-link-alt me-2"></i> View Demo
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <?php if (isLoggedIn()): ?>
                        <button class="btn btn-primary btn-lg add-to-cart-btn" 
                                data-product-id="<?php echo $product['id']; ?>">
                            <i class="fas fa-cart-plus me-2"></i> Add to Cart
                        </button>
                        <a href="checkout.php?product=<?php echo $product['id']; ?>" 
                           class="btn btn-success btn-lg">
                            <i class="fas fa-bolt me-2"></i> Buy Now
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i> Login to Purchase
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6 class="fw-bold mb-3">What's Included:</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Instant Download</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Lifetime Updates</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 24/7 Support</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 30-Day Money Back</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if (!empty($related)): ?>
    <section class="mt-5">
        <h2 class="section-title">Related Products</h2>
        <div class="row g-4">
            <?php foreach ($related as $relProd): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card">
                    <img src="<?php echo $relProd['image'] ? BASE_URL . 'uploads/screenshots/' . $relProd['image'] : 'https://via.placeholder.com/400x300?text=No+Image'; ?>" 
                         class="product-card-img" 
                         alt="<?php echo htmlspecialchars($relProd['title']); ?>">
                    <div class="product-card-body">
                        <h5 class="product-card-title">
                            <?php echo htmlspecialchars($relProd['title']); ?>
                        </h5>
                        <div class="product-card-price">
                            <?php echo formatPrice($relProd['price']); ?>
                        </div>
                        <div class="product-card-footer">
                            <a href="product-detail.php?id=<?php echo $relProd['id']; ?>" 
                               class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Reviews Section -->
    <section class="mt-5">
        <div class="card shadow-custom rounded-custom">
            <div class="card-body p-4">
                <h2 class="fw-bold mb-4">
                    <i class="fas fa-star text-warning me-2"></i>Customer Reviews
                </h2>
                
                <!-- Rating Summary -->
                <div class="row mb-4">
                    <div class="col-md-4 text-center border-end">
                        <h1 class="display-3 fw-bold mb-0"><?php echo number_format($product['average_rating'] ?? 0, 1); ?></h1>
                        <div class="text-warning mb-2">
                            <?php 
                            $avgRating = $product['average_rating'] ?? 0;
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $avgRating) echo '<i class="fas fa-star"></i> ';
                                elseif ($i - 0.5 <= $avgRating) echo '<i class="fas fa-star-half-alt"></i> ';
                                else echo '<i class="far fa-star"></i> ';
                            }
                            ?>
                        </div>
                        <p class="text-muted"><?php echo $product['total_reviews'] ?? 0; ?> reviews</p>
                    </div>
                    <div class="col-md-8">
                        <?php
                        // Rating distribution
                        for ($i = 5; $i >= 1; $i--) {
                            $stmt = $db->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND rating = ? AND status = 'approved'");
                            $stmt->execute([$productId, $i]);
                            $count = $stmt->fetchColumn();
                            $percentage = ($product['total_reviews'] > 0) ? ($count / $product['total_reviews']) * 100 : 0;
                        ?>
                        <div class="d-flex align-items-center mb-2">
                            <span class="me-2" style="width: 60px;"><?php echo $i; ?> star</span>
                            <div class="progress flex-grow-1" style="height: 20px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <span class="ms-2 text-muted" style="width: 50px;"><?php echo $count; ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                
                <!-- Write Review Button -->
                <?php if ($canReview): ?>
                <div class="mb-4">
                    <button class="btn btn-primary" data-mdb-toggle="modal" data-mdb-target="#reviewModal">
                        <i class="fas fa-edit me-2"></i>Write a Review
                    </button>
                </div>
                <?php elseif (isLoggedIn() && $hasPurchased && $hasReviewed): ?>
                <div class="alert alert-info mb-4">
                    <i class="fas fa-check-circle me-2"></i>You have already reviewed this product
                </div>
                <?php elseif (isLoggedIn()): ?>
                <div class="alert alert-warning mb-4">
                    <i class="fas fa-info-circle me-2"></i>Purchase this product to leave a review
                </div>
                <?php endif; ?>
                
                <!-- Reviews List -->
                <?php if (!empty($reviews)): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                    <div class="review-item border-bottom pb-4 mb-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($review['user_name']); ?>&size=50&background=random" 
                                     alt="<?php echo htmlspecialchars($review['user_name']); ?>" 
                                     class="rounded-circle me-3" 
                                     style="width: 50px; height: 50px;">
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                    <div class="text-warning small">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i > $review['rating'] ? '-o' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted"><?php echo formatDate($review['created_at']); ?></small>
                                <?php if ($review['is_verified_purchase']): ?>
                                <div><span class="badge bg-success small">Verified Purchase</span></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($review['title']): ?>
                        <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($review['title']); ?></h6>
                        <?php endif; ?>
                        
                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($review['review'])); ?></p>
                        
                        <div class="d-flex align-items-center">
                            <button class="btn btn-sm btn-outline-secondary review-helpful-btn" 
                                    data-review-id="<?php echo $review['id']; ?>"
                                    <?php echo !isLoggedIn() ? 'disabled' : ''; ?>>
                                <i class="fas fa-thumbs-up me-1"></i>
                                Helpful (<span class="helpful-count"><?php echo $review['helpful_count']; ?></span>)
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Write a Review</h5>
                <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    
                    <div class="mb-4">
                        <label class="form-label">Rating *</label>
                        <div class="rating-input">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                            <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <input type="text" id="reviewTitle" name="title" class="form-control">
                        <label class="form-label" for="reviewTitle">Review Title (Optional)</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <textarea id="reviewText" name="review" class="form-control" rows="5" required></textarea>
                        <label class="form-label" for="reviewText">Your Review *</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i>Submit Review
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
