#!/bin/bash

################################################################################
# Clean Deployment Script for Hostinger
# This script ensures the server matches the GitHub repository EXACTLY
################################################################################

set -e  # Exit on any error

echo "🚀 Starting clean deployment..."
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Clean Git state
echo "📦 Step 1: Cleaning Git state..."
git fetch origin main
git reset --hard origin/main
git clean -fd
echo -e "${GREEN}✓ Git state cleaned${NC}"
echo ""

# Step 2: Remove old files that shouldn't exist
echo "🗑️  Step 2: Removing deprecated files..."
rm -f public/build.zip
rm -f public/test-avatar.html
rm -f public/storage.php
rm -rf storage/app/public/assets
rm -rf storage/app/public/users-avatar
echo -e "${GREEN}✓ Deprecated files removed${NC}"
echo ""

# Step 3: Install dependencies
echo "📚 Step 3: Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓ Dependencies installed${NC}"
echo ""

# Step 4: Storage symlink
echo "🔗 Step 4: Creating storage symlink..."
# Remove existing symlink if it exists
rm -f public/storage

# Try artisan command first
if php artisan storage:link 2>/dev/null; then
    echo -e "${GREEN}✓ Storage symlink created via artisan${NC}"
else
    # If exec() is disabled, create manually
    ln -sfn ../storage/app/public public/storage
    echo -e "${GREEN}✓ Storage symlink created manually${NC}"
fi
echo ""

# Step 5: Clear all caches
echo "🧹 Step 5: Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo -e "${GREEN}✓ All caches cleared${NC}"
echo ""

# Step 6: Cache for production
echo "⚡ Step 6: Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓ Production caches generated${NC}"
echo ""

# Step 7: Verify critical files
echo "🔍 Step 7: Verifying critical files..."

ERRORS=0

# Check logo
if [ ! -f "public/assets/pod-logo.png" ]; then
    echo -e "${RED}✗ Missing: public/assets/pod-logo.png${NC}"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✓ Logo exists ($(du -h public/assets/pod-logo.png | cut -f1))${NC}"
fi

# Check build assets
if [ ! -f "public/build/manifest.json" ]; then
    echo -e "${RED}✗ Missing: public/build/manifest.json${NC}"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✓ Build manifest exists${NC}"
fi

# Check storage symlink
if [ ! -L "public/storage" ]; then
    echo -e "${RED}✗ Storage symlink doesn't exist${NC}"
    ERRORS=$((ERRORS + 1))
elif [ ! -e "public/storage" ]; then
    echo -e "${RED}✗ Storage symlink is broken${NC}"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✓ Storage symlink is valid${NC}"
fi

# Check .htaccess
if [ ! -f ".htaccess" ]; then
    echo -e "${YELLOW}⚠ Missing: .htaccess (creating...)${NC}"
    echo '<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>' > .htaccess
    echo -e "${GREEN}✓ .htaccess created${NC}"
else
    echo -e "${GREEN}✓ .htaccess exists${NC}"
fi

echo ""

# Step 8: Set permissions
echo "🔐 Step 8: Setting correct permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo -e "${GREEN}✓ Permissions set${NC}"
echo ""

# Final summary
echo "════════════════════════════════════════════════"
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
    echo ""
    echo "Your site should now match your local repository exactly."
    echo "Visit your domain to verify."
else
    echo -e "${RED}⚠️  Deployment completed with $ERRORS error(s)${NC}"
    echo ""
    echo "Please fix the errors above and run again."
fi
echo "════════════════════════════════════════════════"

