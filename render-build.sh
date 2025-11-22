#!/usr/bin/env bash
# Exit on error
set -o errexit

# Install PHP and Composer dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Generate application key if not set
php artisan key:generate --force

# Install Node.js dependencies and build assets
npm install
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
php artisan migrate --force
