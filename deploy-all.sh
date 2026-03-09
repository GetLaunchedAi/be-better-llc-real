#!/usr/bin/env bash
# ==========================================================================
# Be Better BSBL — Full Stack Deployment Script
# For use on Cloudways (PHP Custom Application)
# ==========================================================================

set -euo pipefail

# 1. Pull latest code
if [ -d ".git" ]; then
    echo "▸ Pulling latest code..."
    git pull --ff-only origin main || echo "  ⚠ Git pull failed. Continuing..."
fi

# 2. Frontend Build (Eleventy)
echo "▸ Building Frontend (Eleventy)..."
npm install
npm run build

# 3. Backend Setup (Laravel)
echo "▸ Setting up Backend (Laravel)..."
cd laravel

if [ ! -f ".env" ]; then
    echo "  ⚠ .env file missing in laravel/! Copying example..."
    cp .env.example .env
    echo "  ⚠ Please configure .env manually!"
fi

# Install Composer dependencies
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Run migrations
php artisan migrate --force

# Cache config/routes/views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Link storage
php artisan storage:link 2>/dev/null || true

cd ..

# 4. Deployment Logic
# Move _site contents to public_html root (where this script is running)
echo "▸ Deploying Frontend assets..."
cp -r _site/* .

# Ensure .htaccess handles routing
echo "▸ Updating .htaccess..."
if [ -f "root-htaccess" ]; then
    cp root-htaccess .htaccess
fi

# 5. Permissions
echo "▸ Setting permissions..."
chmod -R 775 laravel/storage laravel/bootstrap/cache
if [ -d "_site" ]; then
    chmod -R 775 _site
fi

echo "✓ Deployment complete!"
echo "  - Frontend: Served from root"
echo "  - Backend:  Served from /laravel/public"

