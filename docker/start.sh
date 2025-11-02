#!/bin/bash

# Exit immediately if a command exits with a non-zero status.
set -e

# Wait for the database to be ready
sleep 5

# Run database migrations and seed
php artisan migrate:fresh --seed --force

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisord.conf
