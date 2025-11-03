# 🔌 Laravel Reverb Real-Time Setup Guide

Complete guide to set up Laravel Reverb for real-time chat on your Laravel application.

## 📋 Prerequisites

- Hosting with server access (VPS, DigitalOcean, Railway, etc.)
- PHP 8.2+ installed
- Composer installed
- Ability to run background processes

## 🔧 Step 1: Generate Reverb Credentials

```bash
cd /path/to/your/project

php artisan tinker
```

In tinker:
```php
use Illuminate\Support\Str;
echo "REVERB_APP_KEY=" . Str::random(40) . "\n";
echo "REVERB_APP_SECRET=" . Str::random(40) . "\n";
echo "REVERB_APP_ID=" . Str::random(20) . "\n";
exit;
```

Copy the output and add to your `.env` file.

## 📝 Step 2: Configure .env File

Add these to your `.env`:

```env
# Broadcasting Configuration
BROADCAST_CONNECTION=reverb

# Laravel Reverb Configuration
REVERB_APP_KEY=your-generated-key-here
REVERB_APP_SECRET=your-generated-secret-here
REVERB_APP_ID=your-generated-app-id-here
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

# Make sure APP_URL is set correctly
APP_URL=https://your-domain.com
```

**Important Notes:**
- For production: Use `REVERB_PORT=443` and `REVERB_SCHEME=https`
- For local: Use `REVERB_PORT=8080` and `REVERB_SCHEME=http`
- `REVERB_HOST` should be your domain without `http://` or `https://`

## 🚀 Step 3: Clear and Cache Config

```bash
php artisan config:clear
php artisan config:cache
php artisan route:cache
```

## 🎯 Step 4: Start Reverb Server

### Option A: Manual Start (Testing)

```bash
php artisan reverb:start
```

This will run in foreground. Press `Ctrl+C` to stop.

### Option B: Background Process (Production)

```bash
nohup php artisan reverb:start > storage/logs/reverb.log 2>&1 &
```

Check if it's running:
```bash
ps aux | grep reverb
```

### Option C: Using PM2 (Recommended for Production)

If you have Node.js installed:

```bash
# Install PM2 globally
npm install -g pm2

# Start Reverb with PM2
pm2 start "php artisan reverb:start" --name pod-web-reverb

# Save PM2 configuration
pm2 save

# Make PM2 auto-start on reboot
pm2 startup
# Follow the instructions it prints
```

### Option D: Systemd Service (Best for Production)

Create a systemd service file:

```bash
sudo nano /etc/systemd/system/pod-web-reverb.service
```

Add this content:
```ini
[Unit]
Description=People of Data Reverb WebSocket Server
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/path/to/your/project
ExecStart=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=443
StandardOutput=append:/path/to/your/project/storage/logs/reverb.log
StandardError=append:/path/to/your/project/storage/logs/reverb.log

[Install]
WantedBy=multi-user.target
```

Replace `/path/to/your/project` with your actual project path.

Enable and start:
```bash
sudo systemctl daemon-reload
sudo systemctl enable pod-web-reverb
sudo systemctl start pod-web-reverb
sudo systemctl status pod-web-reverb
```

## ✅ Step 5: Verify Reverb is Running

```bash
# Check if process is running
ps aux | grep reverb

# Check if port is listening
sudo netstat -tlnp | grep 443
# or
sudo lsof -i :443

# Check Reverb logs
tail -f storage/logs/reverb.log
```

## 🔍 Step 6: Test WebSocket Connection

1. Open your chat page in browser
2. Open browser Developer Tools (F12)
3. Go to Console tab
4. You should see: `Pusher : : ["State changed","initialized -> connecting"]`
5. Then: `Pusher : : ["State changed","connecting -> connected"]` ✅

If you see connection errors, check:
- Reverb server is running
- Port 443 is accessible (check firewall)
- `REVERB_HOST` matches your domain
- SSL certificate is valid (if using HTTPS)

## 🐛 Troubleshooting

### Reverb Won't Start

**Error: Port already in use**
```bash
# Find what's using the port
sudo lsof -i :443

# Kill the process or use a different port
REVERB_PORT=8080
```

**Error: Permission denied**
```bash
# Make sure you're using a user that can run PHP
# For systemd, use www-data or your web server user
```

### WebSocket Connection Fails

**Check firewall:**
```bash
# Ubuntu/Debian
sudo ufw allow 443/tcp
sudo ufw reload

# CentOS/RHEL
sudo firewall-cmd --add-port=443/tcp --permanent
sudo firewall-cmd --reload
```

**Check if Reverb is accessible:**
```bash
curl https://your-domain.com:443
# Should connect (may show an error, but connection should work)
```

**Check browser console for specific errors**

### Reverb Keeps Stopping

Use systemd or PM2 for auto-restart on crashes.

## 🔄 Step 7: Update Deployment Script

Add Reverb restart to your deployment script:

```bash
# After deployment
sudo systemctl restart pod-web-reverb
# or
pm2 restart pod-web-reverb
```

## 📱 Step 8: Mobile App Setup

For mobile apps, use the same Reverb credentials. See `docs/API_MOBILE_SETUP.md` for mobile SDK setup.

## ✅ Verification Checklist

- [ ] Reverb credentials generated and added to `.env`
- [ ] `BROADCAST_CONNECTION=reverb` in `.env`
- [ ] Config cached (`php artisan config:cache`)
- [ ] Reverb server is running (check with `ps aux | grep reverb`)
- [ ] Port 443 is open (check firewall)
- [ ] WebSocket connects in browser console
- [ ] Messages appear in real-time
- [ ] Systemd service created (if using systemd)
- [ ] Auto-restart configured

---

## 🎉 You're Done!

Your real-time chat should now work perfectly on both web and mobile apps!

**Need help?** Check logs:
- Reverb logs: `storage/logs/reverb.log`
- Laravel logs: `storage/logs/laravel.log`
- System logs: `journalctl -u pod-web-reverb -f`
