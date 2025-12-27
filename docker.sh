#!/bin/bash

# Tourno Docker Helper Script

case "$1" in
    "start")
        echo "🚀 Starting Tourno services..."
        docker-compose up -d
        echo "✅ Services started!"
        echo "📍 App: http://localhost:8000"
        echo "📍 Adminer: http://localhost:8080"
        ;;

    "stop")
        echo "🛑 Stopping Tourno services..."
        docker-compose down
        echo "✅ Services stopped!"
        ;;

    "restart")
        echo "🔄 Restarting Tourno services..."
        docker-compose restart
        echo "✅ Services restarted!"
        ;;

    "build")
        echo "🔨 Building Tourno app image..."
        docker build --network=host -t tourno-api:v1.0.0 -t tourno-api:latest .
        echo "✅ Build completed!"
        echo "Now starting services..."
        docker-compose up -d
        ;;

    "logs")
        if [ -z "$2" ]; then
            echo "📋 Showing all logs..."
            docker-compose logs -f
        else
            echo "📋 Showing logs for $2..."
            docker-compose logs -f "$2"
        fi
        ;;

    "shell")
        echo "🐚 Opening shell in app container..."
        docker-compose exec app bash
        ;;

    "db")
        echo "🐘 Connecting to PostgreSQL..."
        docker-compose exec db psql -U tourno_user -d tourno
        ;;

    "artisan")
        shift
        echo "⚡ Running artisan command: $@"
        docker-compose exec app php artisan "$@"
        ;;

    "migrate")
        echo "📊 Running migrations..."
        docker-compose exec app php artisan migrate
        echo "✅ Migrations completed!"
        ;;

    "migrate:fresh")
        echo "🔄 Running fresh migrations..."
        docker-compose exec app php artisan migrate:fresh --seed
        echo "✅ Fresh migrations completed!"
        ;;

    "seed")
        echo "🌱 Running seeders..."
        docker-compose exec app php artisan db:seed
        echo "✅ Seeding completed!"
        ;;

    "composer")
        shift
        echo "📦 Running composer command: $@"
        docker-compose exec app composer "$@"
        ;;

    "test")
        echo "🧪 Running tests..."
        docker-compose exec app php artisan test
        ;;

    "clear")
        echo "🧹 Clearing caches..."
        docker-compose exec app php artisan cache:clear
        docker-compose exec app php artisan config:clear
        docker-compose exec app php artisan route:clear
        docker-compose exec app php artisan view:clear
        echo "✅ Caches cleared!"
        ;;

    "permissions")
        echo "🔐 Fixing permissions..."
        docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
        docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
        echo "✅ Permissions fixed!"
        ;;

    "backup")
        BACKUP_FILE="backup_$(date +%Y%m%d_%H%M%S).sql"
        echo "💾 Creating database backup: $BACKUP_FILE"
        docker-compose exec db pg_dump -U tourno_user tourno > "$BACKUP_FILE"
        echo "✅ Backup created: $BACKUP_FILE"
        ;;

    "restore")
        if [ -z "$2" ]; then
            echo "❌ Please provide backup file: ./docker.sh restore backup.sql"
        else
            echo "📥 Restoring database from: $2"
            cat "$2" | docker-compose exec -T db psql -U tourno_user -d tourno
            echo "✅ Database restored!"
        fi
        ;;

    "status")
        echo "📊 Docker containers status:"
        docker-compose ps
        ;;

    "clean")
        echo "🧹 Cleaning Docker resources..."
        read -p "⚠️  This will remove containers and volumes. Continue? (y/n) " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            docker-compose down -v
            echo "✅ Cleanup completed!"
        else
            echo "❌ Cleanup cancelled"
        fi
        ;;

    "install")
        echo "📦 Installing Tourno..."
        echo "Step 1: Building app image..."
        docker build --network=host -t tourno-api:v1.0.0 -t tourno-api:latest .

        echo "Step 2: Starting containers..."
        docker-compose up -d

        echo "Step 3: Installing composer dependencies (if needed)..."
        docker-compose exec app composer install --no-interaction

        echo "Step 4: Setting up environment..."
        if [ ! -f .env ]; then
            cp .env.docker .env
            docker-compose exec app php artisan key:generate
        fi

        echo "Step 5: Running migrations and seeders..."
        docker-compose exec app php artisan migrate:fresh --seed

        echo "Step 6: Fixing permissions..."
        docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
        docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

        echo ""
        echo "✅ Installation completed!"
        echo "📍 App: http://localhost:8000"
        echo "📍 Adminer: http://localhost:8080"
        ;;

    *)
        echo "🐳 Tourno Docker Helper"
        echo ""
        echo "Usage: ./docker.sh [command]"
        echo ""
        echo "Commands:"
        echo "  install           - Complete installation (first time setup)"
        echo "  start             - Start all services"
        echo "  stop              - Stop all services"
        echo "  restart           - Restart all services"
        echo "  build             - Rebuild and start services"
        echo "  logs [service]    - Show logs (all or specific service)"
        echo "  shell             - Open shell in app container"
        echo "  db                - Connect to PostgreSQL"
        echo "  artisan [cmd]     - Run artisan command"
        echo "  migrate           - Run migrations"
        echo "  migrate:fresh     - Fresh migrations with seed"
        echo "  seed              - Run seeders"
        echo "  composer [cmd]    - Run composer command"
        echo "  test              - Run tests"
        echo "  clear             - Clear all caches"
        echo "  permissions       - Fix storage permissions"
        echo "  backup            - Backup database"
        echo "  restore [file]    - Restore database from backup"
        echo "  status            - Show containers status"
        echo "  clean             - Remove containers and volumes"
        echo ""
        echo "Examples:"
        echo "  ./docker.sh install"
        echo "  ./docker.sh start"
        echo "  ./docker.sh artisan route:list"
        echo "  ./docker.sh logs app"
        echo "  ./docker.sh backup"
        ;;
esac
