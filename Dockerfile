# Dockerfile
FROM php:8.2-fpm-alpine

# System deps (including Node.js)
RUN apk add --no-cache git curl bash icu-dev libzip-dev libpq-dev oniguruma-dev autoconf g++ make nodejs npm

# PHP extensions
RUN docker-php-ext-install intl bcmath pcntl pdo pdo_pgsql zip
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Fix git safe directory globally
RUN git config --global --add safe.directory /var/www/html

# Create user with same UID as host (for Linux/Mac)
ARG USER_ID=1000
ARG GROUP_ID=1000
RUN addgroup -g ${GROUP_ID} -S appgroup && \
    adduser -u ${USER_ID} -S appuser -G appgroup

# Set permissions
RUN chown -R appuser:appgroup /var/www/html

# Switch to non-root user
USER appuser

# Optional: faster dev file-watches
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

# Copy composer files first
COPY --chown=appuser:appgroup composer.json composer.lock ./

# Install dependencies before copying source code
RUN composer install --no-dev --no-scripts --no-autoloader

# Copy source code
COPY --chown=appuser:appgroup . .

# Generate autoloader
RUN composer dump-autoload --optimize
