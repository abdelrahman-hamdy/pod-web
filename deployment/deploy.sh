#!/bin/bash

# Laravel Deployment Script for Hostinger
# Run this from your server's public_html directory

set -e

echo "🚀 Starting Laravel deployment..."

# Pull latest changes (if using git)
if [ -d ".git" ]; then
    echo "📥 Pulling latest changes..."
    git pull origin main
fi

# Install/update dependencies
echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache for production
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Create storage link
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Build frontend assets
if [ -f "package.json" ]; then
    echo "🎨 Building frontend assets..."
    npm ci --production
    npm run build
fi

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chown -R $(whoami):$(whoami) storage bootstrap/cache

echo "✅ Deployment complete!"

