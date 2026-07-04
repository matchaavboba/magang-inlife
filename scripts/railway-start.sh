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

# Cache config and views (skip route:cache to avoid duplicate name issues)
php artisan config:cache || true
php artisan view:cache || true

# Configure nginx to listen on Railway's dynamic PORT (default 80)
APP_PORT=${PORT:-80}
echo "==> Configuring nginx on port ${APP_PORT}..."
sed -i "s/listen 80;/listen ${APP_PORT};/" /etc/nginx/nginx.conf

echo "==> Starting nginx + php-fpm via supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
