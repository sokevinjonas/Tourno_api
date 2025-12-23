#!/bin/bash

echo "🔧 Fixing file permissions for Docker Laravel project..."

# Set ownership (if running with sudo)
# Uncomment if you want to change ownership to www-data
# sudo chown -R www-data:www-data .

# Directories should be 755 (rwxr-xr-x)
echo "📁 Setting directory permissions to 755..."
find . -type d -exec chmod 755 {} \;

# Files should be 644 (rw-r--r--)
echo "📄 Setting file permissions to 644..."
find . -type f -exec chmod 644 {} \;

# Make scripts executable
echo "⚙️ Making scripts executable..."
chmod +x artisan
chmod +x fix-permissions.sh
[ -f "docker/entrypoint.sh" ] && chmod +x docker/entrypoint.sh

# Storage and cache directories need to be writable
echo "💾 Setting writable permissions for storage and cache..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod -R 664 storage/logs/*.log 2>/dev/null || true

# Fix specific directories
echo "📂 Fixing specific Laravel directories..."
chmod -R 755 app
chmod -R 755 config
chmod -R 755 database
chmod -R 755 resources
chmod -R 755 routes
chmod -R 755 public

# Make sure critical files are readable
echo "✅ Ensuring critical files are readable..."
chmod 644 .env 2>/dev/null || true
chmod 644 composer.json
chmod 644 composer.lock 2>/dev/null || true
chmod 644 package.json 2>/dev/null || true

echo "✨ Permissions fixed successfully!"
echo ""
echo "ℹ️  To run this script in the future:"
echo "   ./fix-permissions.sh"
echo ""
echo "ℹ️  Or add to your git hooks by creating .git/hooks/post-merge:"
echo "   #!/bin/bash"
echo "   ./fix-permissions.sh"
