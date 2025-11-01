# Hostinger Laravel Deployment Guide

## Prerequisites
- Hostinger account with a hosting plan
- SSH access enabled
- Domain configured
- PHP 8.4+ support
- Database credentials

---

## Step 1: Access Hostinger Control Panel

1. Log in to [Hostinger Control Panel](https://hpanel.hostinger.com)
2. Select your domain
3. Open **File Manager** or connect via **FTP/SSH**

---

## Step 2: Prepare Your Local Project

```bash
# 1. Create production .env file
cp .env.example .env

# 2. Edit .env with production values
APP_NAME="Pod Web"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost  # or IP from Hostinger
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Redis/Cache (if available)
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail (configure based on hosting)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Reverb (WebSockets - disable if not supported)
REVERB_ENABLED=false
REVERB_HOST=127.0.0.1
REVERB_PORT=8080

# Storage
FILESYSTEM_DISK=local

# Optional: Generate app key if needed
php artisan key:generate
```

---

## Step 3: Upload Files to Hostinger

### Option A: Using File Manager
1. In Hostinger panel, open **File Manager**
2. Navigate to `public_html` directory
3. Upload ZIP of your project
4. Extract ZIP file
5. Move **ALL** files from project folder to `public_html` root

### Option B: Using Git (Recommended)
```bash
# On Hostinger server via SSH
cd ~/domains/yourdomain.com/public_html
git clone https://github.com/yourusername/pod-web.git .

# Or if files already there
cd ~/domains/yourdomain.com/public_html
git pull origin main
```

### Option C: Using FTP (WinSCP, FileZilla)
1. Connect via FTP credentials from Hostinger
2. Upload all files to `public_html` directory
3. Ensure hidden files (.env, .gitignore) are transferred

---

## Step 4: Fix Directory Structure for Shared Hosting

**CRITICAL:** Shared hosting requires special structure!

### Current Structure (not suitable):
```
public_html/
  ├── public/
  │   └── index.php
  ├── app/
  ├── config/
  └── ...
```

### Required Structure for Shared Hosting:
```
public_html/
  ├── index.php (from public/)
  ├── .htaccess (from public/)
  ├── app/
  ├── bootstrap/
  ├── config/
  └── ...
```

### Script to Restructure (Run on Server):
```bash
cd ~/domains/yourdomain.com/public_html

# Move contents of public/ to root
mv public/* public/.* . 2>/dev/null || true

# Remove empty public directory
rmdir public

# Create symlink for storage (if supported)
php artisan storage:link
```

---

## Step 5: Configure .htaccess Files

### Root .htaccess (public_html/.htaccess):
```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP Settings
<IfModule mod_php.c>
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
    php_value memory_limit 256M
    php_value max_execution_time 300
</IfModule>
```

### Backup Public .htaccess (storage/)
Create `.htaccess` in storage directories:
```apache
# storage/app/.htaccess
Deny from all

# storage/framework/.htaccess
Deny from all

# storage/logs/.htaccess
Deny from all
```

---

## Step 6: Configure Database

### Create Database in Hostinger:
1. Go to **Databases** → **MySQL Databases**
2. Click **Create New Database**
3. Note credentials: DB_NAME, DB_USER, DB_PASSWORD

### Import Database:
```bash
# Via SSH
cd ~/domains/yourdomain.com/public_html
mysql -u your_db_user -p your_db_name < database_export.sql

# OR via Hostinger phpMyAdmin
# - Go to Databases → phpMyAdmin
# - Select your database
# - Click Import → Choose file → database_export.sql
```

### Or Run Migrations:
```bash
cd ~/domains/yourdomain.com/public_html
php artisan migrate --force
```

---

## Step 7: Set Permissions

```bash
# Via SSH
cd ~/domains/yourdomain.com/public_html

# Set ownership (replace YOUR_USERNAME)
chown -R YOUR_USERNAME:YOUR_USERNAME .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Special permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod 755 public
```

---

## Step 8: Run Production Commands

```bash
cd ~/domains/yourdomain.com/public_html

# Install dependencies (if needed)
composer install --no-dev --optimize-autoloader

# Generate app key
php artisan key:generate --force

# Clear all caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force

# Optimize
php artisan optimize

# Generate storage link
php artisan storage:link

# Build assets (if using npm)
npm install --production
npm run build
```

---

## Step 9: Configure PHP Version

1. In Hostinger panel, go to **Advanced** → **PHP Configuration**
2. Select **PHP 8.4** (or latest available)
3. Enable required extensions:
   - `pdo_mysql`
   - `mbstring`
   - `zip`
   - `xml`
   - `gd`
   - `curl`
   - `fileinfo`
   - `intl`
   - `exif`

---

## Step 10: SSL Certificate

1. Go to **SSL** in Hostinger panel
2. Click **Install SSL** or **Let's Encrypt**
3. Activate for your domain
4. Force HTTPS in `.env`:
```bash
APP_URL=https://yourdomain.com
APP_FORCE_HTTPS=true
```

---

## Step 11: Disable Debugging & Optimize

```bash
cd ~/domains/yourdomain.com/public_html

# In .env
APP_DEBUG=false
APP_ENV=production

# Clear debug caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Step 12: Configure Email

### SMTP Settings (Hostinger):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Pod Web"
```

---

## Step 13: Test Deployment

Visit these URLs to verify:
- `https://yourdomain.com` - Homepage
- `https://yourdomain.com/login` - Login page
- `https://yourdomain.com/api/v1/posts` - API test
- `https://yourdomain.com/docs` - API docs

---

## Common Issues & Fixes

### 500 Internal Server Error
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan config:clear
php artisan cache:clear

# Check permissions
chmod -R 775 storage bootstrap/cache
```

### File Upload Issues
```bash
# Check PHP upload limits
php -i | grep upload_max_filesize

# Increase in .htaccess or php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Route Not Found
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache
```

### Storage Links Broken
```bash
# Recreate storage link
rm public/storage
php artisan storage:link
```

### Database Connection Error
- Verify database credentials in `.env`
- Check database exists in Hostinger panel
- Verify MySQL extension is enabled

---

## Security Checklist

- [ ] APP_DEBUG=false in production
- [ ] Strong DB password
- [ ] HTTPS enabled
- [ ] Storage permissions secured
- [ ] .env file not publicly accessible
- [ ] vendor/ directory not in public_html
- [ ] Regular backups configured

---

## Maintenance Commands

```bash
cd ~/domains/yourdomain.com/public_html

# Daily cache refresh
php artisan optimize:clear && php artisan optimize

# Clear old logs (monthly)
truncate storage/logs/laravel.log

# Check application status
php artisan about
```

---

## Automated Deployment Script

Create `deploy.sh` on server:
```bash
#!/bin/bash
cd ~/domains/yourdomain.com/public_html
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan storage:link
```

---

## Contact & Support

- Hostinger Support: https://www.hostinger.com/contact
- Laravel Docs: https://laravel.com/docs
- Project Repository: [Your GitHub URL]

---

## Notes

- **Never** commit `.env` to git
- **Always** backup before deployment
- **Test** on staging first if possible
- **Monitor** logs regularly
- **Update** dependencies monthly

