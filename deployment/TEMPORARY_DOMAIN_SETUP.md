# Using Hostinger Temporary Subdomain for Testing

## 🆓 Free Testing Domain Available!

Hostinger provides a **free temporary subdomain** for every hosting account, perfect for testing your Laravel application before purchasing a custom domain.

---

## How to Get Your Temporary URL

### Step 1: Log In
1. Go to [Hostinger Control Panel](https://hpanel.hostinger.com)
2. Log in with your credentials

### Step 2: Find Your Temporary Domain
1. In your dashboard, look for **"Temporary Domain"** or **"Testing URL"**
2. It will look like one of these:
   - `yourusername.hostingersite.com`
   - `yoursite.000webhostapp.com` (older accounts)
   - `yourdomain.temporary-url.com`

### Step 3: Note It Down
Copy the full temporary URL - you'll need it for deployment!

---

## Configuration

### In Your `.env` File:

```env
# Use your temporary subdomain
APP_URL=https://yourusername.hostingersite.com

# Everything else remains the same
APP_NAME="Pod Web"
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

---

## Deployment Process (Same as Custom Domain!)

Follow the main deployment guide with these changes:

### Upload Files:
```bash
# Using your temporary URL
ssh user@yourusername.hostingersite.com
cd ~/domains/yourusername.hostingersite.com/public_html
git clone https://github.com/yourusername/pod-web.git .
```

### Database Setup:
Create database using Hostinger panel - works exactly the same!

### SSL Certificate:
- Hostinger automatically provides SSL for temporary domains
- Just ensure `APP_URL` uses `https://`

---

## Advantages of Testing First

✅ **Try Before You Buy:**
- Test your app thoroughly on temporary domain
- Verify all features work correctly
- Check database connections and migrations
- Test file uploads and storage

✅ **Easy Migration:**
- Later, when you get a custom domain, just update `.env`
- No code changes needed!
- Same database and files

✅ **No Extra Cost:**
- Temporary domain is completely free
- Included with all hosting plans
- No expiration (unless you cancel hosting)

---

## Later: Switching to Custom Domain

When you're ready to use a custom domain:

1. **Purchase/Configure Domain** in Hostinger panel
2. **Update `.env`**:
   ```env
   APP_URL=https://yourcustomdomain.com
   ```
3. **Clear caches**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
4. **Done!** Your app is live on the custom domain

**Note:** You might need to update database table domains or any hardcoded URLs, but the core Laravel app will work immediately.

---

## Testing Checklist

Use your temporary domain to verify:

- [ ] Homepage loads correctly
- [ ] Authentication works (login/register)
- [ ] Database queries function
- [ ] File uploads work
- [ ] Email sending works (if configured)
- [ ] API endpoints respond
- [ ] Admin panel accessible
- [ ] All CRUD operations work
- [ ] Search functionality works
- [ ] Pagination works
- [ ] Error pages display (404, 500)
- [ ] Mobile responsive design

---

## Common Issues

### SSL Certificate Not Active
**Solution:** Wait 5-10 minutes after deployment for Hostinger to provision SSL

### Mixed Content Warnings
**Solution:** Ensure all URLs in your app use `https://` or relative paths

### Domain Not Resolving
**Solution:** 
- Check DNS propagation: https://dnschecker.org
- Verify temporary domain is active in Hostinger panel
- Contact Hostinger support if issue persists

---

## Need Help?

- **Hostinger Support:** 24/7 live chat in your panel
- **Documentation:** Full guide in `docs/HOSTINGER_DEPLOYMENT_GUIDE.md`
- **Quick Reference:** `docs/DEPLOYMENT_QUICK_START.md`

---

## Summary

✅ **You CAN deploy without a custom domain!**
✅ **Hostinger provides free temporary URL**
✅ **Perfect for testing phase**
✅ **Easy to migrate to custom domain later**
✅ **No additional cost**

**Start with the temporary domain, test everything, then upgrade to custom domain when ready!** 🚀

