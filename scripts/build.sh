#!/bin/bash
set -e

echo "🔨 Building Mzizination for production..."

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node dependencies (if using frontend)
if [ -f "package.json" ]; then
    echo "📦 Installing Node dependencies..."
    npm install --production
    # Build frontend assets
    npm run build
fi

# Cache configuration
echo "⚙️ Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "✅ Build complete"
