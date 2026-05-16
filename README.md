# Digital Marketplace - Complete eCommerce Platform

A fully responsive digital product selling website built with PHP, MySQL, and MDB Bootstrap. Features both user-facing storefront and comprehensive admin dashboard.

## 🚀 Features

### User Features
- **Authentication System**
  - User registration and login
  - Password reset functionality
  - Profile management
  
- **Product Browsing**
  - Responsive product listing with filters
  - Category-based navigation
  - Advanced search functionality
  - Product detail pages with image galleries
  
- **Shopping & Checkout**
  - Shopping cart management
  - Coupon/discount code system
  - Multiple payment gateway support (Razorpay, Stripe, PayPal)
  - Secure checkout process
  
- **Order Management**
  - Order history and tracking
  - Secure download system with expiry
  - Invoice generation
  - Download limits per product
  
- **Support System**
  - Contact form
  - FAQ page
  - Support ticket system

### Admin Features
- **Dashboard**
  - Sales analytics and reports
  - Revenue tracking
  - User statistics
  - Top-selling products
  
- **Product Management**
  - Add/edit/delete products
  - Upload digital files
  - Multiple screenshot uploads
  - Category management
  - Product status control
  
- **Order Management**
  - View all orders
  - Update order status
  - Process refunds
  - Transaction tracking
  
- **User Management**
  - View registered users
  - Block/unblock users
  - View purchase history
  
- **Coupon System**
  - Create discount codes
  - Flat or percentage discounts
  - Set expiry dates
  - Usage limits
  
- **Settings**
  - Payment gateway configuration
  - Tax settings (GST, VAT)
  - Email/SMTP configuration
  - Download settings
  - Site branding

## 🎨 Design Features

### Responsive Design
- **Mobile View**: Native app-like experience with bottom navigation
- **Desktop View**: Professional website layout with sidebar navigation
- **Tablet View**: Optimized layouts for medium screens
- **Dark/Light Mode**: Toggle between themes with persistent storage

### UI Components
- Material Design Bootstrap (MDB) components
- Font Awesome icons
- Smooth animations and transitions
- Touch-friendly mobile interface
- Professional admin panel

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP (for local development)

## 🛠️ Installation

1. **Clone or Download** the project to your web server directory:
   ```
   C:\xampp\htdocs\MDB store\
   ```

2. **Create Database**:
   - Open phpMyAdmin
   - Create a new database named `digital_marketplace`
   - Import the schema: `database/schema.sql`

3. **Configure Database**:
   - Edit `config/database.php`
   - Update database credentials if needed:
     ```php
     private $host = "localhost";
     private $db_name = "digital_marketplace";
     private $username = "root";
     private $password = "";
     ```

4. **Set Permissions**:
   - Ensure `uploads/` directory is writable (755 or 777)

5. **Access the Site**:
   - Frontend: `http://localhost/MDB%20store/`
   - Admin: `http://localhost/MDB%20store/admin/`

## 🔐 Default Login Credentials

### Admin Account
- **Email**: admin@marketplace.com
- **Password**: admin123

*Note: Change these credentials immediately after first login!*

## 📁 Project Structure

```
MDB store/
├── admin/                  # Admin panel
│   ├── assets/            # Admin CSS/JS
│   ├── includes/          # Admin header/footer
│   ├── index.php          # Dashboard
│   ├── products.php       # Product management
│   ├── orders.php         # Order management
│   ├── users.php          # User management
│   ├── coupons.php        # Coupon management
│   ├── support.php        # Support tickets
│   └── settings.php       # System settings
├── api/                   # AJAX API endpoints
│   ├── add-to-cart.php
│   ├── remove-from-cart.php
│   ├── cart-count.php
│   └── cart-total.php
├── assets/                # Frontend assets
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   └── js/
│       └── main.js        # Main JavaScript
├── config/                # Configuration files
│   ├── config.php         # Main config
│   └── database.php       # Database connection
├── database/              # Database files
│   └── schema.sql         # Database schema
├── includes/              # Common includes
│   ├── header.php
│   └── footer.php
├── uploads/               # Upload directory
│   ├── products/          # Product files
│   └── screenshots/       # Product images
├── index.php              # Homepage
├── products.php           # Product listing
├── product-detail.php     # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout page
├── orders.php             # User orders
├── profile.php            # User profile
├── login.php              # Login page
├── signup.php             # Registration
├── contact.php            # Contact form
├── faq.php                # FAQ page
└── logout.php             # Logout
```

## 🎯 Key Features Explained

### Payment Gateway Integration
The system supports three major payment gateways:
- **Razorpay**: For Indian market (UPI, Cards, Net Banking)
- **Stripe**: International credit/debit cards
- **PayPal**: Global payment solution

Configure in Admin → Settings → Payment Gateway

### Download Security
- Downloads are protected with unique tokens
- Automatic expiry after configured days
- Limited number of downloads per purchase
- Secure file delivery system

### Coupon System
Create flexible discount codes:
- **Flat discounts**: Fixed amount off
- **Percentage discounts**: % off total
- Minimum purchase requirements
- Usage limits and expiry dates

### Responsive Design
- **Desktop**: Full navbar, grid layouts, sidebar navigation
- **Mobile**: Bottom navigation bar, vertical card lists, touch-optimized
- **Adaptive**: Automatically adjusts to screen size

## 🔧 Configuration

### Payment Gateway Setup

1. **Razorpay**:
   - Sign up at razorpay.com
   - Get API keys from dashboard
   - Add to Admin → Settings

2. **Stripe**:
   - Create account at stripe.com
   - Get publishable and secret keys
   - Configure in settings

3. **PayPal**:
   - Create business account
   - Get client ID and secret
   - Set mode (sandbox/live)

### Email Configuration
Configure SMTP settings in Admin → Settings:
- SMTP Host (e.g., smtp.gmail.com)
- SMTP Port (587 for TLS)
- Username and Password
- Enable/disable email notifications

## 🎨 Customization

### Changing Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #1266f1;
    --secondary-color: #b23cfd;
    /* Add your colors */
}
```

### Adding Categories
1. Go to Admin → Categories
2. Add new categories
3. Assign to products

### Modifying Layout
- Edit `includes/header.php` for navigation
- Edit `includes/footer.php` for footer content
- Modify `assets/css/style.css` for styling

## 📱 Mobile Features

### Bottom Navigation
- Home, Products, Cart, Profile tabs
- Active state indicators
- Badge notifications for cart items

### App Bar
- Fixed top bar with logo
- Theme toggle
- Cart icon with count

### Touch Optimization
- Large touch targets (44px minimum)
- Swipe-friendly cards
- Native-like animations

## 🔒 Security Features

- Password hashing with PHP password_hash()
- SQL injection prevention with prepared statements
- XSS protection with htmlspecialchars()
- CSRF token implementation ready
- Secure file upload validation
- Session management

## 📊 Admin Analytics

Dashboard provides:
- Total revenue tracking
- Order statistics
- User growth metrics
- Top-selling products
- Recent orders overview
- Support ticket status

## 🐛 Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

### Upload Issues
- Check folder permissions (uploads/)
- Verify PHP upload_max_filesize setting
- Check post_max_size in php.ini

### Payment Gateway Not Working
- Verify API keys are correct
- Check gateway is set to active
- Ensure SSL certificate for live mode

## 📝 License

This project is open-source and available for personal and commercial use.

## 🤝 Support

For issues and questions:
- Create support ticket through contact form
- Check FAQ page for common questions
- Review documentation

## 🚀 Future Enhancements

Planned features:
- Multi-vendor support
- Advanced analytics
- Email marketing integration
- Social media login
- Product reviews and ratings
- Wishlist functionality
- Affiliate system

## 📞 Contact

For technical support or inquiries, use the contact form on the website.

---

**Built with ❤️ using PHP, MySQL, and MDB Bootstrap**
