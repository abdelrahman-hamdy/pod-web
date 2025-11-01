# Quick Deployment Checklist

## ✅ Pre-Deployment

- [ ] Test app locally: `php artisan test`
- [ ] Build assets: `npm run build`
- [ ] Export database: `mysqldump -u user -p database > backup.sql`
- [ ] Update `.env` for production
- [ ] **Get your Hostinger temporary URL** (or domain)

---

## 🆓 Using Temporary Subdomain

**No custom domain?** Hostinger provides a free temporary URL:
- Log in to Hostinger Panel
- Find your temporary subdomain: `yourusername.hostingersite.com`
- Use this for testing!
- You can switch to a custom domain later

---

## 🚀 Quick Deploy Steps

### 1. Upload Files
```bash
# Via SSH (use your temporary URL or custom domain)
ssh user@your-username.hostingersite.com
cd ~/domains/your-username.hostingersite.com/public_html
git clone https://github.com/yourusername/pod-web.git .
```

### 2. Configure Environment
```bash
cp deployment/env.production.example .env
# Edit .env with production values
php artisan key:generate --force
```

### 3. Setup Database
```bash
# Create DB in Hostinger panel, then:
mysql -u user -p database < backup.sql
# OR
php artisan migrate --force
```

### 4. Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
chmod -R 755 .
```

### 5. Install & Optimize
```bash
composer install --no-dev --optimize-autoloader
php artisan optimize
php artisan storage:link
```

### 6. Fix .htaccess
```bash
cp deployment/.htaccess.root .htaccess
cp deployment/.htaccess.storage storage/app/.htaccess
cp deployment/.htaccess.storage storage/framework/.htaccess
cp deployment/.htaccess.storage storage/logs/.htaccess
```

---

## 🔍 Verify

- [ ] Homepage loads
- [ ] Login works
- [ ] API responses
- [ ] File uploads work
- [ ] Emails send

---

## 🛠️ Common Fixes

```bash
# 500 Error
tail -f storage/logs/laravel.log
php artisan config:clear && php artisan optimize

# Permissions
chmod -R 775 storage bootstrap/cache

# Route issues
php artisan route:clear && php artisan route:cache

# Storage links
php artisan storage:link
```

---

## 📞 Need Help?

- Check logs: `storage/logs/laravel.log`
- Enable debug temporarily: `APP_DEBUG=true` in `.env`
- Hostinger support: Check panel → Support

