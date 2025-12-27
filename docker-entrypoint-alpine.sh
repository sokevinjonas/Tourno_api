#!/bin/sh
# Alpine entrypoint for Laravel with Supervisor

set -e

echo "🔧 Setting up Laravel application..."

# Create log directories
mkdir -p /var/log/php-fpm /var/log/nginx /var/log/supervisor
chown -R nginx:nginx /var/log/php-fpm

# Fix permissions for storage and cache directories
echo "📁 Fixing storage and cache permissions..."
chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure critical directories have proper permissions
echo "📂 Setting file permissions..."
find /var/www/html/app -type d -exec chmod 755 {} \; 2>/dev/null || true
find /var/www/html/app -type f -exec chmod 644 {} \; 2>/dev/null || true
find /var/www/html/config -type f -exec chmod 644 {} \; 2>/dev/null || true
find /var/www/html/routes -type f -exec chmod 644 {} \; 2>/dev/null || true
find /var/www/html/resources/views -type f -exec chmod 644 {} \; 2>/dev/null || true

# Ensure public directory is readable
chmod 755 /var/www/html/public

echo "✅ Setup complete!"

# Start supervisord to manage PHP-FPM and Nginx
echo "🚀 Starting services with Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
