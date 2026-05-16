# Quick Start Guide - Digital Marketplace

Get your digital marketplace up and running in 5 minutes!

## 🚀 Quick Installation (5 Steps)

### Step 1: Extract Files
Extract the project to your web server:
```
C:\xampp\htdocs\MDB store\
```

### Step 2: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database: `digital_marketplace`
3. Import: `database/schema.sql`

### Step 3: Configure
Edit `config/database.php` (usually no changes needed for XAMPP):
```php
private $host = "localhost";
private $db_name = "digital_marketplace";
private $username = "root";
private $password = "";
```

### Step 4: Start Server
- Start Apache and MySQL in XAMPP Control Panel

### Step 5: Access
- **Frontend**: `http://localhost/MDB%20store/`
- **Admin**: `http://localhost/MDB%20store/admin/`

## 🔐 Default Login

**Admin Account**
- Email: `admin@marketplace.com`
- Password: `admin123`

⚠️ **Change password immediately after first login!**

## ✅ First Steps After Installation

### 1. Login to Admin Panel
```
http://localhost/MDB%20store/admin/
```

### 2. Change Admin Password
- Go to Profile → Change Password

### 3. Configure Settings
Go to **Admin → Settings** and update:
- ✅ Site Name
- ✅ Site Email
- ✅ Currency (USD, EUR, INR, etc.)
- ✅ Tax Rates (if applicable)

### 4. Add Your First Product
1. Go to **Admin → Products → Add New Product**
2. Fill in details:
   - Title
   - Description
   - Price
   - Category
3. Upload product file (ZIP, PDF, etc.)
4. Upload screenshots
5. Click **Save Product**

### 5. Test the System
1. Create a user account (frontend)
2. Add product to cart
3. Go through checkout process
4. Verify order in admin panel

## 🎨 Customization Quick Tips

### Change Site Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #1266f1;  /* Change this */
    --secondary-color: #b23cfd; /* Change this */
}
```

### Add Logo
1. Place logo in `assets/images/logo.png`
2. Update in `includes/header.php`

### Configure Payment Gateway

**For Razorpay:**
1. Sign up at razorpay.com
2. Get API keys
3. Add to Admin → Settings → Payment Gateway

**For Stripe:**
1. Sign up at stripe.com
2. Get API keys
3. Add to Admin → Settings → Payment Gateway

## 📱 Testing Responsive Design

### Desktop View
- Open in browser normally
- Professional navbar at top
- Grid product layouts

### Mobile View
- Open browser DevTools (F12)
- Toggle device toolbar
- Select mobile device
- See bottom navigation bar

### Dark Mode
- Click moon/sun icon in navbar
- Theme persists across sessions

## 🔧 Common Tasks

### Add Category
```sql
INSERT INTO categories (name, slug, description, status) 
VALUES ('Your Category', 'your-category', 'Description', 'active');
```

### Create Coupon
1. Go to **Admin → Coupons**
2. Click **Create Coupon**
3. Set discount type and value
4. Set expiry (optional)
5. Save

### View Orders
- **Admin**: Admin → Orders
- **User**: Profile → My Orders

### Manage Users
- Go to **Admin → Users**
- View, search, block/unblock users

## 📊 Admin Dashboard Overview

The dashboard shows:
- 💰 Total Revenue
- 🛒 Total Orders
- 📦 Total Products
- 👥 Total Users
- ⏳ Pending Orders
- 🎫 Open Support Tickets
- 📈 Recent Orders
- 🏆 Top Selling Products

## 🎯 Key Features at a Glance

### For Users
✅ Browse products with filters
✅ Search functionality
✅ Add to cart
✅ Apply discount coupons
✅ Secure checkout
✅ Multiple payment options
✅ Download purchased products
✅ Order history
✅ Profile management

### For Admins
✅ Dashboard with analytics
✅ Product management
✅ Order management
✅ User management
✅ Coupon system
✅ Support tickets
✅ System settings
✅ Payment gateway config

## 🔒 Security Checklist

Before going live:
- [ ] Change admin password
- [ ] Update database credentials
- [ ] Configure SMTP for emails
- [ ] Set up SSL certificate
- [ ] Enable HTTPS redirect
- [ ] Test payment gateway
- [ ] Backup database
- [ ] Set proper file permissions

## 📞 Need Help?

### Documentation
- 📖 README.md - Overview
- 🛠️ INSTALLATION.md - Detailed setup
- ✨ FEATURES.md - Complete feature list
- 📝 CHANGELOG.md - Version history

### Troubleshooting
1. Check INSTALLATION.md troubleshooting section
2. Verify all installation steps completed
3. Check error logs
4. Test with default settings

### Support
- Use contact form on website
- Check FAQ page
- Review documentation

## 🎉 You're Ready!

Your digital marketplace is now set up and ready to sell products!

### Next Steps:
1. ✅ Add more products
2. ✅ Customize design
3. ✅ Configure payment gateway
4. ✅ Test complete purchase flow
5. ✅ Add FAQ content
6. ✅ Set up email notifications
7. ✅ Launch your store!

---

**Happy Selling! 🚀**

For detailed information, see:
- **README.md** - Project overview
- **INSTALLATION.md** - Detailed installation guide
- **FEATURES.md** - Complete feature list
