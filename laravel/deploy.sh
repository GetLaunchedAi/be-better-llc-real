#!/usr/bin/env bash
# ==========================================================================
# Be Better BSBL — Laravel Production Deployment Script
# For use on Cloudways or similar Apache/PHP hosting
#
# Usage:
#   chmod +x deploy.sh
#   ./deploy.sh
#
# Environment: expects APP_ENV=production in .env
# ==========================================================================

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

echo "╔══════════════════════════════════════════════╗"
echo "║  Be Better BSBL — Production Deploy          ║"
echo "╚══════════════════════════════════════════════╝"
echo ""

# --------------------------------------------------------------------------
# 1. Pre-flight checks
# --------------------------------------------------------------------------
echo "▸ Pre-flight checks..."

if [ ! -f ".env" ]; then
    echo "  ✗ .env file not found. Copy .env.example and configure."
    exit 1
fi

if ! php -v > /dev/null 2>&1; then
    echo "  ✗ PHP not found in PATH."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
echo "  ✓ PHP ${PHP_VERSION}"

if ! command -v composer > /dev/null 2>&1; then
    echo "  ✗ Composer not found in PATH."
    exit 1
fi

echo "  ✓ Composer found"
echo ""

# --------------------------------------------------------------------------
# 2. Maintenance mode
# --------------------------------------------------------------------------
echo "▸ Enabling maintenance mode..."
php artisan down --retry=60 --render="errors.503-cutover" 2>/dev/null || true
echo "  ✓ App in maintenance mode"
echo ""

# --------------------------------------------------------------------------
# 3. Pull latest code (if using git)
# --------------------------------------------------------------------------
if [ -d ".git" ]; then
    echo "▸ Pulling latest code..."
    git pull --ff-only origin main 2>/dev/null || {
        echo "  ⚠ Git pull failed or not on main. Continuing with current code."
    }
    echo ""
fi

# --------------------------------------------------------------------------
# 4. Install/update dependencies
# --------------------------------------------------------------------------
echo "▸ Installing Composer dependencies (production)..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
echo "  ✓ Dependencies installed"
echo ""

# --------------------------------------------------------------------------
# 5. Run database migrations
# --------------------------------------------------------------------------
echo "▸ Running database migrations..."
php artisan migrate --force
echo "  ✓ Migrations complete"
echo ""

# --------------------------------------------------------------------------
# 6. Production optimizations
# --------------------------------------------------------------------------
echo "▸ Running production optimizations..."

# Cache config
php artisan config:cache
echo "  ✓ Config cached"

# Cache routes
php artisan route:cache
echo "  ✓ Routes cached"

# Cache views
php artisan view:cache
echo "  ✓ Views cached"

# Clear old caches
php artisan cache:clear
echo "  ✓ Application cache cleared"

echo ""

# --------------------------------------------------------------------------
# 7. Storage link
# --------------------------------------------------------------------------
echo "▸ Ensuring storage link..."
php artisan storage:link 2>/dev/null || true
echo "  ✓ Storage linked to public"
echo ""

# --------------------------------------------------------------------------
# 8. File permissions (Cloudways standard)
# --------------------------------------------------------------------------
echo "▸ Setting file permissions..."
find storage bootstrap/cache -type d -exec chmod 775 {} \; 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} \; 2>/dev/null || true
echo "  ✓ Permissions set"
echo ""

# --------------------------------------------------------------------------
# 9. Bring app back up
# --------------------------------------------------------------------------
echo "▸ Disabling maintenance mode..."
php artisan up
echo "  ✓ App is live"
echo ""

# --------------------------------------------------------------------------
# 10. Post-deploy health check
# --------------------------------------------------------------------------
echo "▸ Running smoke test..."
php artisan cutover:smoke-test 2>/dev/null || {
    echo "  ⚠ Smoke test reported issues (see output above)."
    echo "  The app is live, but manual verification is recommended."
}

echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║  ✓ Deployment complete!                       ║"
echo "╚══════════════════════════════════════════════╝"
echo ""
echo "  Quick commands:"
echo "    php artisan cutover:smoke-test   — Re-run smoke tests"
echo "    php artisan backup:run           — Create backup"
echo "    php artisan down                 — Maintenance mode"
echo "    php artisan up                   — Back online"

