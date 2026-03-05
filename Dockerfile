# Stage 1: Install PHP dependencies (needs vendor for Tailwind/Filament config in Vite)
FROM composer:latest AS composer-build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-scripts --ignore-platform-reqs --no-interaction

# Stage 2: Build frontend assets (requires vendor/filament for tailwind.config.js)
FROM node:22-slim AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
COPY --from=composer-build /app/vendor ./vendor
RUN npm run build

# Stage 3: Final PHP application
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git zip unzip pkg-config libicu-dev sqlite3 libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_sqlite intl zip opcache pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY --from=composer-build /app/vendor ./vendor
COPY . .
COPY --from=node-build /app/public/build ./public/build

# Re-run autoload dump with proper PHP (composer-build used --no-scripts)
RUN composer dump-autoload --optimize --no-dev --no-scripts

RUN chmod -R 775 storage bootstrap/cache \
    && mkdir -p database \
    && touch database/database.sqlite

EXPOSE 8080

CMD ["sh", "-c", "php artisan package:discover --ansi && php artisan config:cache && php artisan migrate --force --no-interaction && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
