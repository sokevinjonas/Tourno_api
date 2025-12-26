#!/bin/bash

echo "🚀 Starting Laravel Queue Worker..."

# Fix permissions for storage and cache directories
echo "📁 Fixing storage and cache permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure critical directories are readable
echo "📂 Ensuring application files are readable..."
chmod -R 755 /var/www/html/app 2>/dev/null || true
chmod -R 755 /var/www/html/config 2>/dev/null || true
chmod -R 755 /var/www/html/database 2>/dev/null || true

echo "✅ Setup complete!"

# Start queue worker
exec php /var/www/html/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=3600
