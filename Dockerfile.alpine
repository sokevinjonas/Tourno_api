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
# Stage 2: Production runtime (Alpine - ULTRA LEAN)
# ==========================================
FROM php:8.4-apache-alpine AS production

# Install runtime dependencies using apk (Alpine package manager)
RUN apk add --no-cache \
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
    # Apache needs these
    apache2-utils

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

# Enable Apache mod_rewrite
RUN sed -i 's/#LoadModule rewrite_module/LoadModule rewrite_module/' /etc/apache2/httpd.conf

# Configure Apache DocumentRoot to point to Laravel's public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/localhost/htdocs!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/httpd.conf

# Allow .htaccess overrides
RUN sed -i '/<Directory "\/var\/www\/localhost\/htdocs">/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/httpd.conf

# Set working directory
WORKDIR /var/www/html

# Copy application from builder (includes optimized vendor without dev)
# Alpine uses 'apache:apache' instead of 'www-data:www-data'
COPY --from=builder-bookworm --chown=apache:apache /var/www/html /var/www/html

# Copy entrypoint script (Alpine version)
COPY docker-entrypoint-alpine.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions (Alpine uses 'apache' user instead of 'www-data')
RUN chown -R apache:apache /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Use custom entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
