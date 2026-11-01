#!/bin/bash
set -e

echo "🔨 Building Mzizination for production..."

# Laravel's writable directories (not committed to git)
mkdir -p storage/logs storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# The frontend bundle (public/js/app.js) is committed, so no Node build is needed here.
# To rebuild it locally: npm ci --legacy-peer-deps && NODE_OPTIONS=--openssl-legacy-provider npm run dev

if [ -z "$APP_KEY" ]; then
    echo "❌ APP_KEY is not set. Add it in Render's environment (generate one with: php artisan key:generate --show)."
    exit 1
fi

# Cache configuration
echo "⚙️ Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "✅ Build complete"
