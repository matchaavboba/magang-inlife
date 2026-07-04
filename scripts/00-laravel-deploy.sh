#!/usr/bin/env bash
echo "Running deployment script..."

# Install composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction --working-dir=/var/www/html

# Install NPM dependencies and build Vite assets
npm install
npm run build

# Clear and cache configurations
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force

# Run database seeders (for initial user accounts and roles setup)
# Check if users table is empty to avoid duplicate seeding
php artisan db:seed --force

echo "Deployment complete!"
