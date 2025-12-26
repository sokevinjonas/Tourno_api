# ==========================================
# Stage 1: Build stage (with dev dependencies)
# ==========================================
FROM php:8.4-apache-bookworm AS builder-bookworm

# Set timezone
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install system dependencies (including -dev for compilation)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for layer caching)
COPY composer.json composer.lock ./

# Install dependencies WITHOUT dev packages
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Run composer scripts (if any)
RUN composer dump-autoload --optimize

# ==========================================
# Stage 2: Production runtime (Alpine FPM + Nginx - ULTRA LEAN)
# ==========================================
FROM php:8.4-fpm-alpine AS production

# Install runtime dependencies and nginx
RUN apk add --no-cache \
    # Nginx web server
    nginx \
    # PostgreSQL client libraries
    postgresql-libs \
    # Image processing
    libpng \
    # String processing
    oniguruma \
    # XML processing
    libxml2 \
    # ZIP support
    libzip \
    # Utilities
    bash \
    curl \
    tzdata \
    ca-certificates \
    # Supervisor to manage nginx + php-fpm
    supervisor

# Install build dependencies temporarily for PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    postgresql-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    $PHPIZE_DEPS \
    # Install PHP extensions
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    # Remove build dependencies to reduce image size
    && apk del .build-deps

# Set timezone
ENV TZ=UTC
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

# Configure PHP-FPM to listen on 127.0.0.1:9000
RUN sed -i 's/listen = 9000/listen = 127.0.0.1:9000/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.owner = nobody/listen.owner = nginx/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/;listen.group = nobody/listen.group = nginx/' /usr/local/etc/php-fpm.d/www.conf

# Set working directory
WORKDIR /var/www/html

# Copy nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Create nginx directories
RUN mkdir -p /var/log/nginx \
    && mkdir -p /var/lib/nginx/tmp \
    && chown -R nginx:nginx /var/log/nginx \
    && chown -R nginx:nginx /var/lib/nginx

# Copy application from builder (includes optimized vendor without dev)
# Alpine uses 'nginx:nginx' for web files with FPM
COPY --from=builder-bookworm --chown=nginx:nginx /var/www/html /var/www/html

# Copy entrypoint script (Alpine FPM version)
COPY docker-entrypoint-alpine.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions
RUN chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Use custom entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
