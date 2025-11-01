# Production Deployment Checklist

**Date:** January 2025  
**Application:** People of Data - Pod Web  
**Version:** Laravel 12, Filament 3

## Pre-Deployment Configuration

### 1. Environment Configuration

#### `.env` File Settings
```bash
# Critical Production Settings
APP_NAME="People Of Data"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY_HERE  # Run: php artisan key:generate
APP_DEBUG=false                     # MUST BE FALSE
APP_URL=https://your-domain.com    # Your production domain

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=secure_password_here

# Cache & Session (Recommended: Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="People Of Data"

# Storage
FILESYSTEM_DISK=public
# Or use S3 for production:
# FILESYSTEM_DISK=s3
# AWS_ACCESS_KEY_ID=...
# AWS_SECRET_ACCESS_KEY=...
# AWS_DEFAULT_REGION=...
# AWS_BUCKET=...

# OAuth (if used)
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URL=https://your-domain.com/auth/google/callback

LINKEDIN_CLIENT_ID=your_client_id
LINKEDIN_CLIENT_SECRET=your_client_secret
LINKEDIN_REDIRECT_URL=https://your-domain.com/auth/linkedin/callback

# Broadcasting (if using Laravel Reverb)
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=warning  # Use 'warning' or 'error' in production
```

### 2. Server Configuration

#### PHP Settings
```ini
memory_limit=256M
max_execution_time=300
max_input_time=300
post_max_size=10M
upload_max_filesize=10M
max_input_vars=3000
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    
    # SSL Configuration
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    
    root /var/www/pod-web/public;
    index index.php index.html;
    
    # Logging
    access_log /var/log/nginx/pod-web-access.log;
    error_log /var/log/nginx/pod-web-error.log;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;
    
    # Laravel Configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 256 16k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
        fastcgi_intercept_errors off;
    }
    
    # Deny access to .env and sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    location ~* (composer\.(json|lock)|package\.(json|lock)|\.git) {
        deny all;
    }
    
    # Asset caching
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 3. Database Setup

```bash
# Create database and user
mysql -u root -p
CREATE DATABASE pod_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'pod_web_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON pod_web.* TO 'pod_web_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# Seed initial data (if needed)
php artisan db:seed --class=DatabaseSeeder
```

### 4. Application Optimization

```bash
# Install production dependencies
composer install --no-dev --optimize-autoloader

# Build frontend assets
npm ci  # Install dependencies
npm run build  # Production build

# Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache  # If using Laravel 11+

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative

# Run framework optimizations
php artisan optimize
```

### 5. Queue Workers Setup

```bash
# Create systemd service for queue workers
sudo nano /etc/systemd/system/pod-web-queue.service
```

```ini
[Unit]
Description=People of Data Queue Worker
After=network.target redis-server.service mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/pod-web/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start queue workers
sudo systemctl enable pod-web-queue
sudo systemctl start pod-web-queue
```

### 6. Laravel Reverb (if using real-time features)

```bash
# Create systemd service for Reverb
sudo nano /etc/systemd/system/pod-web-reverb.service
```

```ini
[Unit]
Description=People of Data Reverb WebSocket Server
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/pod-web/artisan reverb:start --host=0.0.0.0 --port=443

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start Reverb
sudo systemctl enable pod-web-reverb
sudo systemctl start pod-web-reverb
```

### 7. Cron Jobs Setup

```bash
# Edit crontab
crontab -e -u www-data

# Add Laravel scheduler
* * * * * cd /var/www/pod-web && php artisan schedule:run >> /dev/null 2>&1
```

### 8. File Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/pod-web

# Set correct permissions
sudo find /var/www/pod-web -type f -exec chmod 644 {} \;
sudo find /var/www/pod-web -type d -exec chmod 755 {} \;

# Set special permissions for storage and cache
sudo chmod -R 775 /var/www/pod-web/storage
sudo chmod -R 775 /var/www/pod-web/bootstrap/cache

# Secure .env file
sudo chmod 600 /var/www/pod-web/.env
```

### 9. SSL Certificate Setup

#### Let's Encrypt (Free SSL)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal is set up automatically
```

## Deployment Steps

### Step 1: Pull Latest Code
```bash
cd /var/www/pod-web
git pull origin main
```

### Step 2: Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### Step 3: Run Migrations
```bash
php artisan migrate --force
```

### Step 4: Clear and Cache
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 5: Restart Services
```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm

# Restart Nginx
sudo systemctl restart nginx

# Restart queue workers
sudo systemctl restart pod-web-queue

# Restart Reverb (if using)
sudo systemctl restart pod-web-reverb
```

## Post-Deployment Verification

### 1. Application Health Check
```bash
# Test the application
curl -I https://your-domain.com/up

# Should return 200 OK
```

### 2. Test Critical Features
- [ ] User registration
- [ ] User login
- [ ] Password reset
- [ ] Email sending
- [ ] File uploads
- [ ] Real-time chat (if using)
- [ ] Notifications
- [ ] API endpoints

### 3. Security Checks
- [ ] SSL certificate valid
- [ ] Security headers present
- [ ] No debug mode enabled
- [ ] .env file not accessible
- [ ] Sensitive files protected
- [ ] Strong admin passwords
- [ ] CSRF protection working

### 4. Performance Checks
- [ ] Page load times < 3 seconds
- [ ] Database queries optimized
- [ ] Cache working
- [ ] Assets minified
- [ ] Images optimized
- [ ] CDN configured (if using)

### 5. Monitoring Setup
- [ ] Error logging configured
- [ ] Application monitoring (Sentry, Bugsnag, etc.)
- [ ] Server monitoring (UptimeRobot, Pingdom, etc.)
- [ ] Database backup scheduled
- [ ] Log rotation configured

## Ongoing Maintenance

### Daily Tasks
- [ ] Monitor error logs
- [ ] Check server resources
- [ ] Review security alerts

### Weekly Tasks
- [ ] Review application logs
- [ ] Check disk space
- [ ] Update security patches
- [ ] Review user feedback

### Monthly Tasks
- [ ] Database optimization
- [ ] Clear old logs
- [ ] Security audit
- [ ] Performance review
- [ ] Backup verification

## Rollback Procedure

If deployment fails:

```bash
# Revert to previous version
cd /var/www/pod-web
git checkout HEAD~1

# Reinstall dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Clear caches
php artisan optimize:clear

# Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
```

## Emergency Contacts

- **Technical Lead:** [Your Contact]
- **Server Admin:** [Your Contact]
- **Database Admin:** [Your Contact]
- **Hosting Support:** [Provider Contact]

## Notes

- Always test in staging before production
- Keep database backups before migrations
- Monitor error logs closely after deployment
- Document any custom configurations

