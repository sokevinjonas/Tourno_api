# ==========================================
# Stage 1: Builder (Alpine - Installation des dépendances)
# ==========================================
FROM php:8.4-fpm-alpine AS builder

# Install build tools and Composer
RUN apk add --no-cache \
    git \
    unzip \
    curl

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first (for layer caching)
COPY composer.json composer.lock ./

# Install dependencies WITHOUT dev packages
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Run composer scripts
RUN composer dump-autoload --optimize

# ==========================================
# Stage 2: Production runtime (Alpine FPM + Nginx - OPTIMIZED)
# ==========================================
FROM php:8.4-fpm-alpine AS production

# Install ONLY runtime dependencies (minimized for size)
RUN apk add --no-cache \
    nginx \
    postgresql-client \
    libpng \
    libjpeg-turbo \
    oniguruma \
    libxml2 \
    libzip \
    curl \
    tzdata \
    supervisor

# Install build dependencies temporarily for PHP extensions
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    # Configure GD (without WebP/FreeType to save space)
    && docker-php-ext-configure gd \
        --with-jpeg \
    # Install PHP extensions in parallel
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    # Clean up build dependencies to reduce image size
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/apk/*

# Set timezone
ENV TZ=UTC
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

# Set working directory
WORKDIR /var/www/html

# Copy PHP configurations
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf

# Copy Nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

# Copy Supervisor configurations
COPY supervisord.conf /etc/supervisord.conf
COPY docker/supervisor/queue-worker.conf /etc/supervisor/queue-worker.conf

# Create required directories
RUN mkdir -p /var/log/nginx /var/log/php-fpm /var/log/supervisor \
    && mkdir -p /var/lib/nginx/tmp \
    && mkdir -p /run \
    && chown -R nginx:nginx /var/log/nginx /var/log/php-fpm \
    && chown -R nginx:nginx /var/lib/nginx

# Copy application from builder (includes optimized vendor without dev)
COPY --from=builder --chown=nginx:nginx /var/www/html /var/www/html

# Copy entrypoint scripts
COPY docker-entrypoint-alpine.sh /usr/local/bin/docker-entrypoint.sh
COPY docker-queue-entrypoint.sh /usr/local/bin/queue-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/queue-entrypoint.sh

# Set permissions and aggressive cleanup
RUN chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache \
    # Remove all unnecessary files
    && rm -rf /tmp/* /var/cache/apk/* /usr/share/man/* /usr/share/doc/* \
    # Remove unused locales to save space
    && find /usr/share/locale -mindepth 1 -maxdepth 1 ! -name 'en*' ! -name 'locale.alias' -exec rm -rf {} + \
    # Remove PHP test files
    && find /usr/local/lib/php -name tests -type d -exec rm -rf {} + 2>/dev/null || true \
    # Strip binaries to reduce size
    && find /usr/local/bin /usr/local/sbin -type f -exec strip --strip-all {} + 2>/dev/null || true

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Use custom entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
