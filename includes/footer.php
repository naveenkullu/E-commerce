    </main>

    <!-- Footer (Desktop) -->
    <footer class="bg-dark text-white pt-5 pb-4 desktop-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-store"></i> <?php echo getSetting('site_name') ?? 'Digital Marketplace'; ?>
                    </h5>
                    <p class="text-white-50">
                        Your trusted source for premium digital products. Download instantly after purchase.
                    </p>
                    <div class="social-links mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>" class="text-white-50 text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>products.php" class="text-white-50 text-decoration-none">Products</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>faq.php" class="text-white-50 text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>contact.php" class="text-white-50 text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Categories</h6>
                    <ul class="list-unstyled">
                        <?php
                        $stmt = $db->query("SELECT * FROM categories WHERE status = 'active' LIMIT 5");
                        while ($cat = $stmt->fetch()):
                        ?>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>products.php?category=<?php echo $cat['slug']; ?>" 
                               class="text-white-50 text-decoration-none">
                                <?php echo $cat['name']; ?>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                
                <div class="col-md-3 mb-4">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled text-white-50">
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo getSetting('site_email') ?? 'info@marketplace.com'; ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +1 234 567 8900
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123 Market Street, City
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="bg-white-50 my-4">
            
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-white-50">
                        &copy; <?php echo date('Y'); ?> <?php echo getSetting('site_name') ?? 'Digital Marketplace'; ?>. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white-50 text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50 text-decoration-none me-3">Terms of Service</a>
                    <a href="#" class="text-white-50 text-decoration-none">Refund Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation -->
    <?php if (isLoggedIn()): ?>
    <nav class="mobile-bottom-nav">
        <a href="<?php echo BASE_URL; ?>" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo BASE_URL; ?>products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Products</span>
        </a>
        <a href="<?php echo BASE_URL; ?>cart.php" class="nav-item position-relative <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
            <span class="badge rounded-pill badge-notification bg-danger cart-count-bottom">0</span>
        </a>
        <a href="<?php echo BASE_URL; ?>profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>
    <?php endif; ?>

    <!-- MDB Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
    
    <script>
        // Update cart count
        function updateCartCount() {
            fetch('<?php echo BASE_URL; ?>api/cart-count.php')
                .then(response => response.json())
                .then(data => {
                    const count = data.count || 0;
                    document.querySelectorAll('.cart-count, .cart-count-mobile, .cart-count-bottom').forEach(el => {
                        el.textContent = count;
                        el.style.display = count > 0 ? 'inline-block' : 'none';
                    });
                });
        }
        
        // Update cart count on page load
        <?php if (isLoggedIn()): ?>
        updateCartCount();
        <?php endif; ?>
    </script>
</body>
</html>
