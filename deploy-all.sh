#!/usr/bin/env bash
# ==========================================================================
# Be Better BSBL — Full Stack Deployment Script
# For use on Cloudways (PHP Custom Application)
# ==========================================================================

set -uo pipefail  # removed -e so script continues on non-fatal errors

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

# Ensure required directories exist and are writable
mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs storage/app/public
chmod -R 775 bootstrap/cache storage 2>/dev/null || true

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

# 4. Deploy Frontend assets
echo "▸ Deploying Frontend assets..."
cp -r _site/* .

# 5. Remove static files that MUST be handled by Laravel
#    These get recreated by Eleventy but conflict with Laravel routes.
echo "▸ Cleaning up conflicting static files..."
rm -rf admin 2>/dev/null || true         # /admin → Laravel admin panel
rm -f products.json 2>/dev/null || true  # /products.json → Laravel dynamic feed
rm -rf search 2>/dev/null || true        # /search → Laravel search

# 6. Install root .htaccess (routes traffic between static & Laravel)
echo "▸ Updating .htaccess..."
if [ -f "root-htaccess" ]; then
    cp root-htaccess .htaccess
fi

# 7. Permissions (suppress errors — use Cloudways "Reset Permission" if needed)
echo "▸ Setting permissions..."
chmod -R 775 laravel/storage laravel/bootstrap/cache 2>/dev/null || true

# 8. Clear Laravel cache so new routes/config take effect
echo "▸ Clearing Laravel caches..."
cd laravel
php artisan cache:clear 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
cd ..

echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║  ✓ Deployment complete!                      ║"
echo "╠══════════════════════════════════════════════╣"
echo "║  Frontend:  Static pages served from root    ║"
echo "║  Backend:   /admin, /products.json, /search  ║"
echo "║             all routed to Laravel             ║"
echo "╚══════════════════════════════════════════════╝"
echo ""
