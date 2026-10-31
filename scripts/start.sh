#!/bin/bash
set -e

echo "🚀 Starting Mzizination..."

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear

# Run the Laravel scheduler in the background (settles sportsbook bets, TON checks)
echo "⏰ Starting scheduler loop..."
( while true; do php artisan schedule:run --no-interaction >> storage/logs/scheduler.log 2>&1; sleep 60; done ) &

# Start Laravel
echo "📡 Starting Laravel server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
