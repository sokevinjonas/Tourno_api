#!/bin/sh
# Alpine entrypoint for Laravel Queue Worker with Supervisor

set -e

echo "🚀 Starting Laravel Queue Worker..."

# Create log directories
mkdir -p /var/log/supervisor
chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Fix permissions
echo "📁 Fixing permissions..."
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Setup complete!"

# Start supervisord to manage queue workers and scheduler
exec /usr/bin/supervisord -c /etc/supervisor/queue-worker.conf
