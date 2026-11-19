# Multi-stage Dockerfile for Webotapp Logistics ERP (Laravel 12 / PHP 8.4 / Vite)

# ==============================================================
# Stage 1: Base Runtime Image (PHP-FPM + CLI + Extensions + Node)
# ==============================================================
FROM php:8.4-fpm-alpine AS base

# Configurable user/group IDs to match host permissions during development
ARG UID=1000
ARG GID=1000

ENV UID=${UID}
ENV GID=${GID}

# Install essential system libraries & build dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    freetype \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client \
    shadow \
    nodejs \
    npm

# Configure & install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

# Compile and enable PECL Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer from official image
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Match www-data UID/GID with host developer user
RUN groupmod -o -g ${GID} www-data || true && \
    usermod -o -u ${UID} -g www-data www-data || true

# Copy custom PHP configs
COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ensure storage directories exist with proper permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

EXPOSE 9000

CMD ["php-fpm"]


# ==============================================================
# Stage 2: Frontend Asset Builder (Production)
# ==============================================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package*.json vite.config.js ./
RUN npm ci --ignore-scripts || npm install --ignore-scripts

COPY resources ./resources
COPY public ./public

RUN npm run build


# ==============================================================
# Stage 3: Self-Contained Production Release
# ==============================================================
FROM base AS production

WORKDIR /var/www/html

# Copy application source code
COPY . /var/www/html

# Copy compiled frontend assets from frontend-builder
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Install PHP dependencies for production
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-scripts

# Set final production directory permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data

CMD ["php-fpm"]
