#!/bin/bash
set -e

echo "==> Running Laravel startup..."

# Create storage link for public assets
php artisan storage:link --force || true

# Clear caches first
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "==> Running migrations..."
php artisan migrate --force

# Seed database (only if tables are empty)
echo "==> Running seeders..."
php artisan db:seed --force || true

# Cache for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Starting nginx + php-fpm via supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
