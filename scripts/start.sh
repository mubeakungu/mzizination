#!/bin/bash
set -e

echo "🚀 Starting Mzizination..."

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear

# Start Laravel
echo "📡 Starting Laravel server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
