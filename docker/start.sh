#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Force correct permissions for storage and cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
