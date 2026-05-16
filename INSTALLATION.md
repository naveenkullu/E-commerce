# Installation Guide - Digital Marketplace

## Prerequisites

Before installing, ensure you have:
- **XAMPP/WAMP/LAMP** (or similar local server environment)
- **PHP 7.4+**
- **MySQL 5.7+**
- **Web Browser** (Chrome, Firefox, Safari, or Edge)

## Step-by-Step Installation

### 1. Download and Extract

1. Download the project files
2. Extract to your web server directory:
   - **XAMPP**: `C:\xampp\htdocs\MDB store\`
   - **WAMP**: `C:\wamp64\www\MDB store\`
   - **LAMP**: `/var/www/html/MDB store/`

### 2. Create Database

1. Open **phpMyAdmin** in your browser:
   ```
   http://localhost/phpmyadmin
   ```

2. Click on **"New"** in the left sidebar

3. Create a new database:
   - Database name: `digital_marketplace`
   - Collation: `utf8mb4_unicode_ci`
   - Click **"Create"**

### 3. Import Database Schema

1. Select the `digital_marketplace` database

2. Click on the **"Import"** tab

3. Click **"Choose File"** and select:
   ```
   MDB store/database/schema.sql
   ```

4. Click **"Go"** at the bottom

5. Wait for success message: "Import has been successfully finished"

### 4. Configure Database Connection

1. Open `config/database.php` in a text editor

2. Verify/Update the database credentials:
   ```php
   private $host = "localhost";
   private $db_name = "digital_marketplace";
   private $username = "root";
   private $password = "";  // Leave empty for XAMPP default
   ```

3. Save the file

### 5. Set File Permissions

Ensure the `uploads` directory is writable:

**Windows:**
- Right-click on `uploads` folder
- Properties → Security → Edit
- Give "Full Control" to Users

**Linux/Mac:**
```bash
chmod -R 755 uploads/
```

### 6. Start Your Server

**XAMPP:**
1. Open XAMPP Control Panel
2. Start **Apache**
3. Start **MySQL**

**WAMP:**
1. Start WAMP
2. Ensure icon is green

### 7. Access the Website

Open your browser and navigate to:

**Frontend:**
```
http://localhost/MDB%20store/
```

**Admin Panel:**
```
http://localhost/MDB%20store/admin/
```

## Default Login Credentials

### Admin Account
- **Email**: `admin@marketplace.com`
- **Password**: `admin123`

⚠️ **IMPORTANT**: Change these credentials immediately after first login!

## Post-Installation Setup

### 1. Change Admin Password

1. Login to admin panel
2. Go to Profile
3. Change password to something secure

### 2. Configure Site Settings

1. Go to **Admin → Settings**
2. Update:
   - Site Name
   - Site Email
   - Currency Settings
   - Tax Rates

### 3. Configure Payment Gateway

Choose your payment gateway and add credentials:

**For Razorpay:**
1. Sign up at [razorpay.com](https://razorpay.com)
2. Get API Keys from Dashboard
3. Add to Admin → Settings → Payment Gateway

**For Stripe:**
1. Sign up at [stripe.com](https://stripe.com)
2. Get API Keys
3. Add to Admin → Settings → Payment Gateway

**For PayPal:**
1. Create business account at [paypal.com](https://paypal.com)
2. Get Client ID and Secret
3. Add to Admin → Settings → Payment Gateway

### 4. Configure Email (Optional)

For email notifications:

1. Go to **Admin → Settings → Email Settings**
2. Add SMTP credentials:
   - SMTP Host (e.g., smtp.gmail.com)
   - SMTP Port (587 for TLS)
   - Username
   - Password
   - Encryption (TLS/SSL)

**Gmail SMTP Example:**
- Host: `smtp.gmail.com`
- Port: `587`
- Username: `your-email@gmail.com`
- Password: App-specific password
- Encryption: `TLS`

### 5. Add Categories

1. Go to **Admin → Products**
2. The default categories are already created
3. Add more if needed

### 6. Add Your First Product

1. Go to **Admin → Products → Add New Product**
2. Fill in product details
3. Upload product file (ZIP, PDF, etc.)
4. Upload screenshots
5. Set price and category
6. Click **"Save Product"**

## Troubleshooting

### Database Connection Error

**Problem**: "Connection Error: SQLSTATE[HY000] [1049] Unknown database"

**Solution**:
1. Verify database name is `digital_marketplace`
2. Check database exists in phpMyAdmin
3. Re-import `schema.sql` if needed

### Upload Directory Not Writable

**Problem**: "Failed to upload file"

**Solution**:
```bash
# Windows
Right-click uploads folder → Properties → Security → Edit → Full Control

# Linux/Mac
chmod -R 755 uploads/
```

### Page Not Found (404)

**Problem**: "Not Found - The requested URL was not found"

**Solution**:
1. Check `.htaccess` file exists
2. Enable `mod_rewrite` in Apache:
   ```
   XAMPP: Already enabled
   WAMP: Left-click icon → Apache → Apache modules → rewrite_module
   ```

### Blank White Page

**Problem**: Page loads but shows nothing

**Solution**:
1. Enable error reporting in `config/config.php`:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Check PHP error logs
3. Verify all files were extracted correctly

### Payment Gateway Not Working

**Problem**: Payment fails or doesn't process

**Solution**:
1. Verify API keys are correct
2. Check gateway is set to "active" in settings
3. For live mode, ensure SSL certificate is installed
4. Test with sandbox/test mode first

### Images Not Displaying

**Problem**: Product images show broken icon

**Solution**:
1. Check images exist in `uploads/screenshots/`
2. Verify file permissions
3. Check file paths in database
4. Clear browser cache

### Session Issues

**Problem**: Can't login or session expires immediately

**Solution**:
1. Check `session_start()` is called in `config/config.php`
2. Verify PHP session directory is writable
3. Check `php.ini` session settings

## Security Recommendations

### For Production Deployment

1. **Change Database Credentials**
   - Use strong password for MySQL
   - Update in `config/database.php`

2. **Disable Error Display**
   ```php
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

3. **Enable HTTPS**
   - Get SSL certificate
   - Force HTTPS in `.htaccess`

4. **Update Admin Credentials**
   - Change default admin password
   - Use strong, unique password

5. **Secure File Uploads**
   - Limit file types
   - Scan for malware
   - Set maximum file size

6. **Regular Backups**
   - Backup database daily
   - Backup uploaded files
   - Store backups securely

7. **Update PHP Settings**
   ```ini
   upload_max_filesize = 50M
   post_max_size = 50M
   max_execution_time = 300
   ```

## Testing the Installation

### Quick Test Checklist

- [ ] Homepage loads correctly
- [ ] Can view products page
- [ ] Can register new user account
- [ ] Can login with user account
- [ ] Can add product to cart
- [ ] Can view cart
- [ ] Can access checkout
- [ ] Admin panel loads
- [ ] Can login to admin panel
- [ ] Can add new product
- [ ] Can view orders
- [ ] Dark/Light mode toggle works
- [ ] Mobile responsive design works

## Need Help?

If you encounter issues:

1. Check this troubleshooting guide
2. Review error logs in:
   - `C:\xampp\apache\logs\error.log` (XAMPP)
   - Browser console (F12)
3. Verify all installation steps were completed
4. Check file permissions
5. Contact support through the contact form

## Next Steps

After successful installation:

1. ✅ Customize site settings
2. ✅ Add your products
3. ✅ Configure payment gateway
4. ✅ Test complete purchase flow
5. ✅ Set up email notifications
6. ✅ Customize design (colors, logo)
7. ✅ Add FAQ content
8. ✅ Test on mobile devices

---

**Congratulations!** Your Digital Marketplace is now installed and ready to use! 🎉
