# 🆓 Free Hosting Deployment Guide

## 🌟 Best Free Hosting Options

### **1. InfinityFree (Recommended)** ⭐
- ✅ Unlimited bandwidth
- ✅ 5GB storage
- ✅ Free subdomain
- ✅ MySQL database
- ✅ cPanel access
- ✅ No ads
- ❌ Custom domain (paid)

### **2. 000webhost**
- ✅ 300MB storage
- ✅ 3GB bandwidth
- ✅ Free subdomain
- ✅ MySQL database
- ❌ Shows ads

### **3. Freehostia**
- ✅ 250MB storage
- ✅ 6GB bandwidth
- ✅ MySQL database
- ❌ Limited features

---

## 🚀 InfinityFree Deployment (Step-by-Step)

### **Step 1: Create Account**

1. Go to: https://infinityfree.net
2. Click **"Sign Up"**
3. Enter:
   - Email address
   - Password
4. Verify email
5. Login to control panel

---

### **Step 2: Create Website**

1. Click **"Create Account"**
2. Choose subdomain:
   - Example: `ybtdigital.epizy.com`
   - Or: `ybtdigital.rf.gd`
   - Or: `ybtdigital.42web.io`
3. Enter password
4. Click **"Create Account"**
5. Wait 2-5 minutes for activation

---

### **Step 3: Prepare Files**

1. **Update config.php**
   
   Open: `config/config.php`
   
   ```php
   // Database Configuration (Don't change yet - will get from hosting)
   define('DB_HOST', 'sqlXXX.epizy.com'); // Will get this
   define('DB_NAME', 'epiz_XXXXXXX_dbname'); // Will get this
   define('DB_USER', 'epiz_XXXXXXX'); // Will get this
   define('DB_PASS', 'your_password'); // Will get this
   
   // Site Configuration
   define('BASE_URL', 'http://ybtdigital.epizy.com/'); // Your subdomain
   define('SITE_ENV', 'production');
   
   // Disable errors in production
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

2. **Create ZIP file**
   - Select all files in `YBT Digital` folder
   - Right click → Send to → Compressed (zipped) folder
   - Name it: `ybtdigital.zip`

---

### **Step 4: Upload Files**

1. **Login to Control Panel**
   - Go to: https://app.infinityfree.net
   - Login with your credentials

2. **Open File Manager**
   - Click on your website
   - Click **"File Manager"**
   - Or use **"Online File Manager"**

3. **Navigate to htdocs**
   - Open `htdocs` folder
   - Delete default files (index.html, etc.)

4. **Upload ZIP**
   - Click **"Upload"** button
   - Select `ybtdigital.zip`
   - Wait for upload (may take 5-10 minutes)

5. **Extract ZIP**
   - Right click on `ybtdigital.zip`
   - Click **"Extract"**
   - Wait for extraction
   - Delete ZIP file after extraction

---

### **Step 5: Create Database**

1. **Go to Control Panel**
   - Click **"MySQL Databases"**

2. **Create Database**
   - Database Name: `marketplace`
   - Click **"Create Database"**
   - Note down:
     - Database Name: `epiz_XXXXXXX_marketplace`
     - Username: `epiz_XXXXXXX`
     - Password: (your password)
     - Hostname: `sqlXXX.epizy.com`

3. **Open phpMyAdmin**
   - Click **"phpMyAdmin"** button
   - Login with database credentials

4. **Import Database**
   - Click on your database name (left sidebar)
   - Click **"Import"** tab
   - Click **"Choose File"**
   - Select `digital_marketplace.sql` from your computer
   - Scroll down, click **"Go"**
   - Wait for import (may take 2-3 minutes)

5. **Import Additional Tables**
   - Import `add_reviews.sql` same way
   - Import any other SQL files

---

### **Step 6: Update Config File**

1. **Open File Manager**
2. **Navigate to config folder**
3. **Edit config.php**
   - Right click → Edit
   - Update database credentials:
   
   ```php
   define('DB_HOST', 'sqlXXX.epizy.com'); // From step 5
   define('DB_NAME', 'epiz_XXXXXXX_marketplace'); // From step 5
   define('DB_USER', 'epiz_XXXXXXX'); // From step 5
   define('DB_PASS', 'your_password'); // Your password
   define('BASE_URL', 'http://ybtdigital.epizy.com/'); // Your URL
   ```
   
4. **Save file**

---

### **Step 7: Set Permissions**

1. **uploads folder**
   - Right click → Change Permissions
   - Set to: 755

2. **uploads/products folder**
   - Set to: 755

3. **uploads/screenshots folder**
   - Set to: 755

---

### **Step 8: Test Website**

1. **Open your website**
   - Go to: `http://ybtdigital.epizy.com`
   - Should see homepage

2. **Test Login**
   - Go to: `http://ybtdigital.epizy.com/login.php`
   - Login with admin credentials
   - Email: `admin@marketplace.com`
   - Password: `admin123`

3. **Change Admin Password**
   - Go to Profile
   - Change password immediately

4. **Test Features**
   - Browse products
   - Add to cart
   - Checkout
   - Admin panel

---

## 🔧 Common Issues & Solutions

### **Issue 1: Database Connection Error**
```
Solution:
1. Check database credentials in config.php
2. Ensure database exists in phpMyAdmin
3. Verify hostname is correct (sqlXXX.epizy.com)
```

### **Issue 2: White Screen**
```
Solution:
1. Enable error display temporarily:
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
2. Check error message
3. Fix issue
4. Disable errors again
```

### **Issue 3: Images Not Loading**
```
Solution:
1. Check folder permissions (755)
2. Verify BASE_URL is correct
3. Check image paths in database
```

### **Issue 4: Upload Failed**
```
Solution:
1. File size limit: 10MB per file
2. Upload smaller chunks
3. Use FTP instead (FileZilla)
```

### **Issue 5: Slow Loading**
```
Solution:
1. Free hosting is slower
2. Optimize images
3. Enable caching
4. Minimize database queries
```

---

## 📊 InfinityFree Limitations

### **What's Limited:**
- ❌ File size: 10MB per file
- ❌ Daily hits: 50,000
- ❌ Bandwidth: Unlimited but throttled
- ❌ CPU usage: Limited
- ❌ Email: Not supported (use external SMTP)

### **What Works:**
- ✅ PHP 7.4+
- ✅ MySQL database
- ✅ File uploads
- ✅ All basic features
- ✅ Good for testing/demo

---

## 🎯 After Deployment

### **1. Update Admin Settings**
- Login to admin panel
- Go to Settings
- Update:
  - Site Name
  - Site Email (use Gmail)
  - Currency
  - Bank details

### **2. Setup Email (Gmail SMTP)**
```
SMTP Host: smtp.gmail.com
SMTP Port: 587
SMTP Secure: TLS
Username: your-email@gmail.com
Password: App Password
```

### **3. Add Content**
- Add categories
- Add products
- Upload images
- Test everything

### **4. Share Your Site**
- Share URL with friends
- Get feedback
- Make improvements

---

## 💡 Tips for Free Hosting

1. **Backup Regularly**
   - Download files weekly
   - Export database weekly
   - Keep local backup

2. **Optimize Everything**
   - Compress images
   - Minimize code
   - Use caching

3. **Monitor Usage**
   - Check daily hits
   - Monitor bandwidth
   - Avoid heavy traffic

4. **Plan Upgrade**
   - When traffic grows
   - Move to paid hosting
   - Better performance

---

## 🔄 Alternative: GitHub Pages (Static Only)

If you want completely free:
1. Convert to static site
2. Use GitHub Pages
3. Free custom domain
4. Unlimited bandwidth

But: No PHP, No Database ❌

---

## 📱 Mobile Testing

Test on mobile:
1. Open site on phone
2. Check responsive design
3. Test all features
4. Fix any issues

---

## 🆙 When to Upgrade?

Upgrade to paid hosting when:
- ✅ Getting 100+ visitors/day
- ✅ Need faster loading
- ✅ Want custom domain
- ✅ Need email hosting
- ✅ Want better support

**Recommended Upgrade:** Hostinger (₹149/month)

---

## 📝 Quick Reference

### **Your Details:**
```
Website URL: http://ybtdigital.epizy.com
Admin Panel: http://ybtdigital.epizy.com/admin/
Database: phpMyAdmin via control panel
File Manager: Via control panel
```

### **Admin Login:**
```
Email: admin@marketplace.com
Password: admin123 (CHANGE THIS!)
```

### **Support:**
```
InfinityFree Forum: https://forum.infinityfree.net
Documentation: https://infinityfree.net/support
```

---

## 🎉 Deployment Complete!

**Your site is now live on:**
`http://ybtdigital.epizy.com`

**Share with:**
- Friends
- Family
- Social media
- WhatsApp groups

**Get feedback and improve!** 🚀

---

## 🔐 Security Reminder

1. **Change admin password immediately**
2. **Delete create_admin.php**
3. **Use strong passwords**
4. **Backup regularly**
5. **Monitor for issues**

---

**Need Help? Ask me! 💪**

**Happy Deploying! 🎊**
