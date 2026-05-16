// Digital Marketplace - Main JavaScript

// Theme Management
document.addEventListener('DOMContentLoaded', function() {
    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
    
    // Theme toggle buttons
    const themeToggles = document.querySelectorAll('.theme-toggle, .theme-toggle-mobile');
    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    });
    
    function updateThemeIcon(theme) {
        const icons = document.querySelectorAll('.theme-toggle i, .theme-toggle-mobile i');
        icons.forEach(icon => {
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Add to cart animation
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const productId = this.dataset.productId;
            if (productId) {
                addToCart(productId, this);
            }
        });
    });
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Form validation enhancement
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new mdb.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-mdb-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new mdb.Tooltip(tooltip);
    });
    
    // Initialize popovers
    const popovers = document.querySelectorAll('[data-mdb-toggle="popover"]');
    popovers.forEach(popover => {
        new mdb.Popover(popover);
    });
    
    // Initialize MDB Input fields
    const inputs = document.querySelectorAll('.form-outline');
    inputs.forEach((formOutline) => {
        new mdb.Input(formOutline).init();
    });
    
    // Update MDB inputs on value change
    const formInputs = document.querySelectorAll('.form-outline input, .form-outline textarea');
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            const formOutline = this.closest('.form-outline');
            if (formOutline) {
                new mdb.Input(formOutline).update();
            }
        });
    });
    
    // Initialize MDB Dropdowns
    const dropdowns = document.querySelectorAll('[data-mdb-toggle="dropdown"]');
    dropdowns.forEach(dropdown => {
        try {
            new mdb.Dropdown(dropdown);
        } catch (e) {
            console.log('Dropdown init error:', e);
            // Fallback: manual toggle
            dropdown.addEventListener('click', function(e) {
                e.preventDefault();
                const menu = this.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('show');
                }
            });
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
});

// Add to Cart Function
function addToCart(productId, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    fetch('api/add-to-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i> Added!';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            
            // Update cart count
            updateCartCount();
            
            // Show success message
            showToast('Success', 'Product added to cart!', 'success');
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
                button.disabled = false;
            }, 2000);
        } else {
            button.innerHTML = originalText;
            button.disabled = false;
            showToast('Error', data.message || 'Failed to add to cart', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalText;
        button.disabled = false;
        showToast('Error', 'An error occurred. Please try again.', 'danger');
    });
}

// Remove from Cart Function
function removeFromCart(productId, button) {
    if (!confirm('Are you sure you want to remove this item from cart?')) {
        return;
    }
    
    button.disabled = true;
    
    fetch('api/remove-from-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the cart item row
            const cartItem = button.closest('.cart-item');
            if (cartItem) {
                cartItem.style.transition = 'opacity 0.3s ease';
                cartItem.style.opacity = '0';
                setTimeout(() => {
                    cartItem.remove();
                    updateCartTotal();
                    updateCartCount();
                }, 300);
            }
            showToast('Success', 'Item removed from cart', 'success');
        } else {
            button.disabled = false;
            showToast('Error', data.message || 'Failed to remove item', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        showToast('Error', 'An error occurred. Please try again.', 'danger');
    });
}

// Update Cart Total
function updateCartTotal() {
    fetch('api/cart-total.php')
        .then(response => response.json())
        .then(data => {
            const totalElements = document.querySelectorAll('.cart-total');
            totalElements.forEach(el => {
                el.textContent = data.total || '$0.00';
            });
        });
}

// Update Cart Count
function updateCartCount() {
    fetch('api/cart-count.php')
        .then(response => response.json())
        .then(data => {
            const count = data.count || 0;
            document.querySelectorAll('.cart-count, .cart-count-mobile, .cart-count-bottom').forEach(el => {
                el.textContent = count;
                el.style.display = count > 0 ? 'inline-block' : 'none';
            });
        });
}

// Apply Coupon
function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value.trim();
    if (!couponCode) {
        showToast('Error', 'Please enter a coupon code', 'warning');
        return;
    }
    
    const applyBtn = document.getElementById('applyCouponBtn');
    const originalText = applyBtn.innerHTML;
    applyBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
    applyBtn.disabled = true;
    
    fetch('api/apply-coupon.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ coupon_code: couponCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Coupon applied successfully!', 'success');
            location.reload(); // Reload to show updated prices
        } else {
            applyBtn.innerHTML = originalText;
            applyBtn.disabled = false;
            showToast('Error', data.message || 'Invalid coupon code', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        applyBtn.innerHTML = originalText;
        applyBtn.disabled = false;
        showToast('Error', 'An error occurred. Please try again.', 'danger');
    });
}

// Show Toast Notification
function showToast(title, message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    
    const toastId = 'toast-' + Date.now();
    const iconMap = {
        success: 'fa-check-circle',
        danger: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <i class="fas ${iconMap[type]} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-mdb-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = document.getElementById(toastId);
    const toast = new mdb.Toast(toastElement);
    toast.show();
    
    // Remove toast after it's hidden
    toastElement.addEventListener('hidden.mdb.toast', function() {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Search Products
function searchProducts(query) {
    const searchResults = document.getElementById('searchResults');
    if (!searchResults) return;
    
    if (query.length < 2) {
        searchResults.innerHTML = '';
        return;
    }
    
    fetch(`api/search-products.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.products && data.products.length > 0) {
                let html = '<div class="list-group">';
                data.products.forEach(product => {
                    html += `
                        <a href="product-detail.php?id=${product.id}" class="list-group-item list-group-item-action">
                            <div class="d-flex align-items-center">
                                <img src="${product.image}" alt="${product.title}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" class="me-3">
                                <div>
                                    <h6 class="mb-0">${product.title}</h6>
                                    <small class="text-muted">${product.price}</small>
                                </div>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
                searchResults.innerHTML = html;
            } else {
                searchResults.innerHTML = '<div class="alert alert-info">No products found</div>';
            }
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize search with debounce
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function(e) {
        searchProducts(e.target.value);
    }, 300));
}

// Download Product
function downloadProduct(downloadToken, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparing...';
    button.disabled = true;
    
    // Create a hidden iframe to trigger download
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = `api/download-product.php?token=${downloadToken}`;
    document.body.appendChild(iframe);
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
        document.body.removeChild(iframe);
        showToast('Success', 'Download started!', 'success');
    }, 2000);
}

// Initialize Payment Gateway
function initializePayment(orderId, amount, gateway) {
    if (gateway === 'razorpay') {
        initializeRazorpay(orderId, amount);
    } else if (gateway === 'stripe') {
        initializeStripe(orderId, amount);
    } else if (gateway === 'paypal') {
        initializePayPal(orderId, amount);
    }
}

// Razorpay Integration (placeholder)
function initializeRazorpay(orderId, amount) {
    // This would require Razorpay SDK
    console.log('Razorpay payment for order:', orderId, 'Amount:', amount);
    showToast('Info', 'Razorpay integration coming soon', 'info');
}

// Stripe Integration (placeholder)
function initializeStripe(orderId, amount) {
    // This would require Stripe SDK
    console.log('Stripe payment for order:', orderId, 'Amount:', amount);
    showToast('Info', 'Stripe integration coming soon', 'info');
}

// PayPal Integration (placeholder)
function initializePayPal(orderId, amount) {
    // This would require PayPal SDK
    console.log('PayPal payment for order:', orderId, 'Amount:', amount);
    showToast('Info', 'PayPal integration coming soon', 'info');
}

// Image Preview
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Confirm Dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Format Currency
function formatCurrency(amount, currency = '$') {
    return currency + parseFloat(amount).toFixed(2);
}

// Copy to Clipboard
function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            button.innerHTML = originalText;
        }, 2000);
        showToast('Success', 'Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showToast('Error', 'Failed to copy', 'danger');
    });
}

// Back to Top Button
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        if (window.pageYOffset > 300) {
            backToTop.style.display = 'block';
        } else {
            backToTop.style.display = 'none';
        }
    }
});

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Review Form Submission
document.addEventListener('DOMContentLoaded', function() {
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(reviewForm);
            const data = {
                product_id: formData.get('product_id'),
                rating: formData.get('rating'),
                title: formData.get('title'),
                review: formData.get('review')
            };
            
            const submitBtn = reviewForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            fetch('api/submit-review.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('Success', result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('Error', result.message, 'danger');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error', 'Failed to submit review', 'danger');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Review Helpful Button
    const helpfulBtns = document.querySelectorAll('.review-helpful-btn');
    helpfulBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const reviewId = this.dataset.reviewId;
            const countSpan = this.querySelector('.helpful-count');
            
            fetch('api/mark-review-helpful.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({review_id: reviewId})
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    countSpan.textContent = result.count;
                    if (result.action === 'added') {
                        this.classList.add('btn-primary');
                        this.classList.remove('btn-outline-secondary');
                    } else {
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-outline-secondary');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
