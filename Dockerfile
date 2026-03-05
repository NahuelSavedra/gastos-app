FROM php:8.4-cli

# Install system dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git zip unzip curl libicu-dev sqlite3 libzip-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_sqlite intl zip opcache pcntl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies (cached layer)
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-interaction

# Install Node dependencies (cached layer)
COPY package.json package-lock.json ./
RUN npm ci

# Copy full application
COPY . .

# Build frontend assets
RUN npm run build && npm prune --omit=dev

# Permissions
RUN chmod -R 775 storage bootstrap/cache \
    && mkdir -p database \
    && touch database/database.sqlite

EXPOSE 8080

CMD ["sh", "-c", "php artisan config:cache && php artisan migrate --force --no-interaction && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
