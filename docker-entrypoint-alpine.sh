#!/bin/sh
# Note: Alpine uses sh by default, but bash is installed in our Dockerfile

echo "🔧 Setting up Laravel application..."

# Fix permissions for storage and cache directories
# Alpine uses 'apache' user instead of 'www-data'
echo "📁 Fixing storage and cache permissions..."
chown -R apache:apache /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure critical directories are readable by apache
echo "📂 Ensuring application files are readable..."
chmod -R 755 /var/www/html/app 2>/dev/null || true
chmod -R 755 /var/www/html/config 2>/dev/null || true
chmod -R 755 /var/www/html/database 2>/dev/null || true
chmod -R 755 /var/www/html/resources 2>/dev/null || true
chmod -R 755 /var/www/html/routes 2>/dev/null || true
chmod -R 755 /var/www/html/public 2>/dev/null || true

# Make sure view files are readable
echo "👁️ Ensuring view files are readable..."
find /var/www/html/resources/views -type f -name "*.blade.php" -exec chmod 644 {} \; 2>/dev/null || true

echo "✅ Setup complete!"

# Start Apache (httpd in Alpine)
echo "🚀 Starting Apache server..."
exec httpd -D FOREGROUND
