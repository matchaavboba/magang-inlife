#!/bin/bash

echo "==> Running Laravel startup..."

# Create storage link for public assets
php artisan storage:link --force || true

# Clear caches first
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run database migrations
echo "==> Running migrations..."
php artisan migrate --force

# Run seeders - safe with firstOrCreate, errors are non-fatal
echo "==> Running seeders..."
php artisan db:seed --force || echo "==> Seeder skipped or partially completed (safe to ignore)"

# Cache for production performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "==> Starting nginx + php-fpm via supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
