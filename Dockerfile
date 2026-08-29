# Multi-stage build for Mzizination

# Stage 1: Build stage
FROM php:8.2-fpm-alpine as builder

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    libpq-dev \
    postgresql-client \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Stage 2: Runtime stage
FROM php:8.2-fpm-alpine

# Install runtime dependencies
RUN apk add --no-cache \
    libpq \
    nginx \
    supervisor \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql

# Copy PHP configuration
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf

# Copy application
COPY --from=builder /app /app
COPY . /app

WORKDIR /app

# Create necessary directories
RUN mkdir -p storage/logs storage/cache bootstrap/cache \
    && chown -R www-data:www-data /app/storage /app/bootstrap

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Expose port
EXPOSE 8000

# Start PHP-FPM
CMD ["php-fpm"]
