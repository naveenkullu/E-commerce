# 🚀 YBT Digital - Deployment Guide

## 📋 Pre-Deployment Checklist

### ✅ Before Deployment:

- [ ] Test all features locally
- [ ] Update database credentials
- [ ] Change admin password
- [ ] Update site settings
- [ ] Test payment flow
- [ ] Check email configuration
- [ ] Backup database
- [ ] Remove test data

---

## 🌐 Deployment Options

### **Option 1: Shared Hosting (Recommended for Beginners)**
- Hostinger
- Bluehost
- SiteGround
- GoDaddy

### **Option 2: VPS/Cloud**
- DigitalOcean
- AWS
- Google Cloud
- Linode

### **Option 3: Free Hosting (Testing Only)**
- InfinityFree
- 000webhost
- Freehostia

---

## 📦 Step-by-Step Deployment (Shared Hosting)

### **Step 1: Get Hosting & Domain**

1. **Buy Hosting Plan**
   - Minimum: 1GB RAM, 10GB Storage
   - PHP 7.4+ support
   - MySQL database support
   - SSL certificate included

2. **Register Domain**
   - Example: `ybtdigital.com`
   - Or use subdomain: `shop.yourdomain.com`

---

### **Step 2: Prepare Files**

1. **Update Config File**
   
   Edit `config/config.php`:
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   
   // Site Configuration
   define('BASE_URL', 'https://yourdomain.com/');
   define('SITE_ENV', 'production'); // Change from 'development'
   ```

2. **Update .htaccess** (Create if not exists)
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   
   # Remove index.php from URL
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php?/$1 [L]
   ```

3. **Security: Create .htaccess in config folder**
   ```apache
   Deny from all
   ```

---

### **Step 3: Upload Files**

**Using cPanel File Manager:**
1. Login to cPanel
2. Go to File Manager
3. Navigate to `public_html` folder
4. Upload all files (or use ZIP and extract)
5. Set folder permissions:
   - `uploads/` → 755
   - `uploads/products/` → 755
   - `uploads/screenshots/` → 755

**Using FTP (FileZilla):**
1. Download FileZilla
2. Connect using FTP credentials
3. Upload all files to `public_html`
4. Set permissions as above

---

### **Step 4: Setup Database**

1. **Create Database in cPanel**
   - Go to MySQL Databases
   - Create new database: `ybtdigital_db`
   - Create user: `ybtdigital_user`
   - Set strong password
   - Add user to database with ALL PRIVILEGES

2. **Import Database**
   - Go to phpMyAdmin
   - Select your database
   - Click Import
   - Upload `database/digital_marketplace.sql`
   - Click Go

3. **Run Additional SQL Files**
   - Import `database/add_reviews.sql`
   - Import any other SQL files

---

### **Step 5: Configure Settings**

1. **Update Admin Settings**
   - Login to admin panel
   - Go to Settings
   - Update:
     - Site Name
     - Site Email
     - Currency
     - Tax Settings
     - Payment Details

2. **Change Admin Password**
   ```sql
   UPDATE users 
   SET password = 'new_hashed_password' 
   WHERE email = 'admin@marketplace.com';
   ```

3. **Setup Email (SMTP)**
   - Use Gmail SMTP or hosting SMTP
   - Update in admin settings

---

### **Step 6: SSL Certificate**

1. **Enable SSL in cPanel**
   - Go to SSL/TLS
   - Install Let's Encrypt (Free)
   - Or use AutoSSL

2. **Force HTTPS**
   - Already added in .htaccess
   - Test: http://yourdomain.com → should redirect to https://

---

### **Step 7: Final Testing**

✅ **Test These Features:**
- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] Login/Logout works
- [ ] Product browsing
- [ ] Add to cart
- [ ] Checkout process
- [ ] Payment instructions
- [ ] Admin login
- [ ] Admin can approve orders
- [ ] Email notifications
- [ ] File uploads work
- [ ] Images display correctly

---

## 🔒 Security Checklist

### **Must Do:**

1. **Change Default Passwords**
   ```sql
   -- Change admin password
   UPDATE users SET password = PASSWORD_HASH WHERE role = 'admin';
   ```

2. **Disable Error Display**
   ```php
   // In config.php
   if (SITE_ENV === 'production') {
       error_reporting(0);
       ini_set('display_errors', 0);
   }
   ```

3. **Secure Folders**
   - Add .htaccess to sensitive folders
   - Set correct permissions (755 for folders, 644 for files)

4. **Database Security**
   - Use strong passwords
   - Don't use 'root' user
   - Limit privileges

5. **Remove Unnecessary Files**
   - Delete `create_admin.php`
   - Delete `DEPLOYMENT_GUIDE.md`
   - Delete test files

---

## 📧 Email Configuration

### **Gmail SMTP Settings:**
```
SMTP Host: smtp.gmail.com
SMTP Port: 587
SMTP Secure: TLS
Username: your-email@gmail.com
Password: App Password (not regular password)
```

### **Get Gmail App Password:**
1. Go to Google Account Settings
2. Security → 2-Step Verification
3. App Passwords
4. Generate password for "Mail"
5. Use this password in settings

---

## 🎯 Post-Deployment

### **1. Setup Cron Jobs (Optional)**
```bash
# Clear old sessions daily
0 0 * * * php /path/to/your/site/cron/cleanup.php

# Send pending emails
*/5 * * * * php /path/to/your/site/cron/send-emails.php
```

### **2. Setup Backups**
- Daily database backup
- Weekly file backup
- Store backups off-site

### **3. Monitor Site**
- Setup Google Analytics
- Monitor server resources
- Check error logs regularly

### **4. SEO Setup**
- Submit sitemap to Google
- Setup Google Search Console
- Add meta descriptions
- Optimize images

---

## 🐛 Common Issues & Solutions

### **Issue 1: White Screen**
- Check error logs in cPanel
- Enable error display temporarily
- Check file permissions

### **Issue 2: Database Connection Failed**
- Verify database credentials
- Check if database exists
- Ensure user has privileges

### **Issue 3: Images Not Loading**
- Check folder permissions (755)
- Verify BASE_URL is correct
- Check .htaccess rules

### **Issue 4: 500 Internal Server Error**
- Check .htaccess syntax
- Verify PHP version (7.4+)
- Check error logs

### **Issue 5: Email Not Sending**
- Verify SMTP settings
- Check firewall rules
- Test with different SMTP

---

## 📊 Performance Optimization

### **1. Enable Caching**
```apache
# In .htaccess
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### **2. Compress Files**
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/css application/javascript
</IfModule>
```

### **3. Optimize Images**
- Use WebP format
- Compress before upload
- Use lazy loading

### **4. Database Optimization**
- Add indexes to frequently queried columns
- Clean old data regularly
- Optimize tables monthly

---

## 🎉 Launch Checklist

### **Before Going Live:**
- [ ] All features tested
- [ ] SSL certificate active
- [ ] Admin password changed
- [ ] Email working
- [ ] Payment system tested
- [ ] Backup created
- [ ] Error pages customized
- [ ] Privacy policy added
- [ ] Terms & conditions added
- [ ] Contact information updated

### **After Launch:**
- [ ] Submit to Google
- [ ] Share on social media
- [ ] Monitor for 24 hours
- [ ] Check analytics
- [ ] Test from different devices
- [ ] Get user feedback

---

## 📱 Mobile App (Future)

Consider building mobile app using:
- React Native
- Flutter
- Ionic

Use same backend APIs!

---

## 💰 Monetization Tips

1. **Premium Features**
   - Featured listings
   - Priority support
   - Advanced analytics

2. **Commission Model**
   - Take % from each sale
   - Subscription plans

3. **Advertising**
   - Banner ads
   - Sponsored products

---

## 🆘 Support Resources

- **PHP Documentation**: https://php.net
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **MDBootstrap Docs**: https://mdbootstrap.com/docs/
- **Stack Overflow**: https://stackoverflow.com

---

## 🎯 Success Metrics

Track these KPIs:
- Daily active users
- Conversion rate
- Average order value
- Customer retention
- Page load time
- Bounce rate

---

**🚀 Ready to Deploy? Follow this guide step by step!**

**Need Help? Contact: support@ybtdigital.com**

---

## 📝 Quick Deploy Commands

```bash
# Backup database
mysqldump -u username -p database_name > backup.sql

# Compress files
tar -czf ybtdigital-backup.tar.gz /path/to/site

# Upload via SCP
scp -r /local/path user@server:/remote/path

# Set permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
```

---

**Good Luck! 🎉**
