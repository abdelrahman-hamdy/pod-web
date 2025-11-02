# syntax=docker/dockerfile:1

# Base image with PHP and essential tools
FROM php:8.4-fpm-alpine AS base
WORKDIR /var/www/html
RUN apk add --no-cache bash curl git icu-dev libzip-dev
# Add common PHP extensions
RUN docker-php-ext-install pdo pdo_mysql bcmath exif intl zip

# Composer stage - We use the PHP image and install composer manually
FROM base AS composer_stage
WORKDIR /app
# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# Frontend build stage
FROM node:18-alpine AS frontend_stage
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Final application image
FROM base AS app
# Install NGINX and Supervisor for process management
RUN apk add --no-cache nginx supervisor
# Copy application code
COPY . .
# Copy installed vendor dependencies
COPY --from=composer_stage /app/vendor ./vendor
# Copy built frontend assets
COPY --from=frontend_stage /app/public/build ./public/build
# Copy NGINX and Supervisor configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
# Copy entrypoint script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set permissions
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]

