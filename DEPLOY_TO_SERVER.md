# 🚀 Deploy to Server - Step by Step

**Last Updated:** November 3, 2025

This guide ensures your server matches your local repository **EXACTLY**.

---

## 📋 Prerequisites

- SSH access to your Hostinger server
- MySQL database created in Hostinger panel
- Domain pointing to your server

---

## 🎯 Deployment Steps

### **Step 1: SSH into Your Server**

```bash
ssh your-username@your-server-ip
```

### **Step 2: Navigate to Your Project**

```bash
cd ~/domains/your-domain.com/public_html
```

### **Step 3: Run the Clean Deployment Script**

```bash
bash deployment/clean-deploy.sh
```

**This script automatically:**
- ✅ Resets to latest GitHub code
- ✅ Removes old/deprecated files
- ✅ Installs dependencies
- ✅ Creates storage symlink
- ✅ Clears and regenerates caches
- ✅ Verifies all critical files exist

### **Step 4: Configure Environment (First Time Only)**

If this is your first deployment, set up `.env`:

```bash
nano .env
```

**Required settings:**
```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

MAIL_MAILER=log
```

**Save:** `CTRL+X`, then `Y`, then `ENTER`

### **Step 5: Generate App Key (First Time Only)**

```bash
php artisan key:generate
```

### **Step 6: Run Migrations (First Time Only)**

**Option A: With Sample Data (Recommended for Testing)**
```bash
composer install --optimize-autoloader
php artisan migrate:fresh --seed --force
composer install --no-dev --optimize-autoloader
```

**Option B: Clean Production (No Sample Data)**
```bash
php artisan migrate:fresh --force
```

Then create admin user:
```bash
php artisan tinker
```

In tinker:
```php
App\Models\User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'name' => 'Admin User',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('YourSecurePassword123!'),
    'role' => 'admin',
    'email_verified_at' => now(),
    'profile_completed' => true,
    'is_active' => true,
]);
exit
```

### **Step 7: Final Cache**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 8: Verify Deployment**

Visit your domain: `https://your-domain.com`

**Check:**
- ✅ Landing page loads
- ✅ Logo displays correctly
- ✅ All images load
- ✅ Can register/login
- ✅ Dashboard works
- ✅ No 404 or 500 errors

---

## 🔄 Updating After Changes

Whenever you make changes locally and push to GitHub:

```bash
# SSH into server
ssh your-username@your-server-ip
cd ~/domains/your-domain.com/public_html

# Run the clean deployment script
bash deployment/clean-deploy.sh
```

**That's it!** The script handles everything automatically.

---

## 🚨 Troubleshooting

### **Images Not Loading**

```bash
# Recreate storage symlink
rm -f public/storage
ln -sfn ../storage/app/public public/storage

# Verify it's correct
ls -la public/storage
```

### **Old Logo/Assets Still Showing**

```bash
# Force reset to GitHub
git fetch origin main
git reset --hard origin/main
git clean -fd

# Run deployment script
bash deployment/clean-deploy.sh
```

### **"Class not found" Errors**

```bash
composer dump-autoload --optimize
php artisan config:clear
php artisan config:cache
```

### **500 Server Error**

```bash
# Check logs
tail -50 storage/logs/laravel.log

# Clear everything
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### **Routes Not Working**

```bash
# Clear route cache
php artisan route:clear

# Verify .htaccess exists in root
cat .htaccess
```

If missing:
```bash
echo '<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>' > .htaccess
```

---

## 📊 What Gets Deployed

### ✅ **Included in Git (Always Deployed)**
- All PHP code
- All Blade views
- Routes, controllers, models
- **Built assets** (`public/build/`)
- **Static assets** (`public/assets/`)
- Migrations
- Configuration files

### ❌ **Not in Git (Must Configure on Server)**
- `.env` file
- `vendor/` directory (installed via Composer)
- `node_modules/` (not needed on server)
- User uploads in `storage/app/public/`
- Log files
- Cache files

---

## 🔒 Security Checklist

Before going live, verify:

- [ ] `APP_DEBUG=false` in `.env`
- [ ] `APP_ENV=production` in `.env`
- [ ] Strong database password
- [ ] Strong admin user password
- [ ] `storage/` and `bootstrap/cache/` have write permissions
- [ ] `.env` file is not publicly accessible
- [ ] HTTPS is enabled (SSL certificate)

---

## 💡 Pro Tips

1. **Always run the deployment script** - Don't manually git pull and clear caches
2. **Test locally first** - Never push untested code
3. **Backup database before updates** - Just in case
4. **Monitor logs** - Check `storage/logs/laravel.log` regularly
5. **Keep .env secure** - Never commit it to Git

---

## 📞 Need Help?

If you encounter issues:

1. Check the troubleshooting section above
2. Review `storage/logs/laravel.log` on the server
3. Verify `.env` configuration
4. Ensure database credentials are correct
5. Check file permissions on `storage/` and `bootstrap/cache/`

---

**Your server will now always match your local repository. No more surprises!** 🎉

