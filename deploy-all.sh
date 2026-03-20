#!/usr/bin/env bash
# ==========================================================================
# Be Better BSBL — Full Stack Deployment Script
# Cloudways "Deploy via Git" — webroot is _site/
# ==========================================================================

set -uo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$REPO_ROOT"

echo "╔══════════════════════════════════════════════╗"
echo "║  Be Better BSBL — Deploy                     ║"
echo "╚══════════════════════════════════════════════╝"
echo ""

# --------------------------------------------------------------------------
# 1. Frontend Build (Eleventy)
# --------------------------------------------------------------------------
echo "▸ Building Frontend (Eleventy)..."
npm install --production=false
npm run build
echo "  ✓ _site/ built"
echo ""

# --------------------------------------------------------------------------
# 2. Laravel Setup
# --------------------------------------------------------------------------
echo "▸ Setting up Laravel..."
cd laravel

mkdir -p bootstrap/cache storage/framework/{cache,sessions,views} storage/logs storage/app/public
chmod -R 775 bootstrap/cache storage 2>/dev/null || true

if [ ! -f ".env" ]; then
    echo "  ⚠ .env missing in laravel/ — copying .env.example"
    cp .env.example .env
    echo "  ⚠ Configure laravel/.env before going live!"
fi

composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

php artisan migrate --force
echo "  ✓ Migrations complete"

echo "▸ Importing products from JSON..."
php artisan app:import-products-json 2>/dev/null || echo "  ⚠ Import skipped or failed"

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Standard Laravel storage link (laravel/public/storage → ../storage/app/public)
php artisan storage:link 2>/dev/null || true

cd "$REPO_ROOT"
echo "  ✓ Laravel ready"
echo ""

# --------------------------------------------------------------------------
# 3. Create storage symlink inside _site/ (webroot)
#    So /storage/uploads/... resolves to Laravel's public storage disk.
# --------------------------------------------------------------------------
echo "▸ Linking _site/storage → Laravel public storage..."
STORAGE_LINK="_site/storage"
STORAGE_TARGET="../laravel/storage/app/public"

if [ -L "$STORAGE_LINK" ]; then
    rm "$STORAGE_LINK"
fi
ln -s "$STORAGE_TARGET" "$STORAGE_LINK"
echo "  ✓ _site/storage symlinked"
echo ""

# --------------------------------------------------------------------------
# 4. Permissions
# --------------------------------------------------------------------------
echo "▸ Setting permissions..."
chmod -R 775 laravel/storage laravel/bootstrap/cache 2>/dev/null || true
echo "  ✓ Permissions set"
echo ""

# --------------------------------------------------------------------------
# 5. Clear caches so new routes/config take effect
# --------------------------------------------------------------------------
echo "▸ Clearing Laravel caches..."
cd laravel
php artisan cache:clear 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan config:cache 2>/dev/null || true
cd "$REPO_ROOT"
echo "  ✓ Caches refreshed"
echo ""

echo "╔══════════════════════════════════════════════╗"
echo "║  ✓ Deployment complete!                      ║"
echo "╠══════════════════════════════════════════════╣"
echo "║  Webroot:   _site/                           ║"
echo "║  Static:    HTML pages served from _site/    ║"
echo "║  Dynamic:   /admin, /products.json, /search  ║"
echo "║             routed to Laravel via _laravel.php║"
echo "║  Uploads:   _site/storage → Laravel storage  ║"
echo "╚══════════════════════════════════════════════╝"
echo ""
