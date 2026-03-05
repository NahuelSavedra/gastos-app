# Stage 1: Build frontend assets
FROM node:22-slim AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: PHP application
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev sqlite3 libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_sqlite intl zip opcache pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies (cached layer)
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Copy application source
COPY . .

# Copy built frontend assets from node stage
COPY --from=node-build /app/public/build ./public/build

# Permissions + ensure SQLite file exists
RUN chmod -R 775 storage bootstrap/cache \
    && mkdir -p database \
    && touch database/database.sqlite

EXPOSE 8080

CMD ["sh", "-c", "php artisan config:cache && php artisan migrate --force --no-interaction && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
