#!/bin/bash
# Exit immediately if any command returns a non-zero exit status
set -e

echo "🚀 Starting Deployment..."

# 1. Activate Maintenance Mode
echo "🚧 Activating Maintenance Mode..."
php artisan down --message="The system is updating. Please try again in a moment." || true

# 2. Fetch the Latest Code
echo "📥 Pulling latest commits from Git..."
git pull origin main

# 3. Install Composer Dependencies (optimized for production)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 4. Install NPM Dependencies & Build Production Assets
echo "🎨 Building frontend assets..."
if [ -f "package-lock.json" ]; then
    npm ci
else
    npm install
fi
npm run build

# 5. Run Database Migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# 6. Optimize and Cache Configurations
echo "⚡ Caching configurations and routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Restart Queue Workers (Required since queue driver is 'database')
echo "🔄 Restarting queue workers..."
php artisan queue:restart

# 8. Deactivate Maintenance Mode
echo "✨ Deactivating Maintenance Mode..."
php artisan up

echo "✅ Deployment finished successfully!"
