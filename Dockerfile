# ---- Frontend build ----
FROM node:22-alpine AS frontend
WORKDIR /fe
COPY frontend/package.json frontend/package-lock.json* ./
RUN npm install
COPY frontend/ ./
# API URL is empty so browser uses same-origin /api (proxied by nginx)
ENV VITE_API_URL=
RUN npm run build

# ---- PHP app ----
FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libpq-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nginx supervisor curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_pgsql pgsql zip gd bcmath pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/composer.json backend/composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader || \
    composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

COPY backend/ ./
COPY --from=frontend /fe/dist ./public/spa

# SPA assets into public; index fallback handled by nginx
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/public bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php -r "file_exists('.env') || copy('.env.example', '.env');" \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi || true

COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh \
    && ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/sites-enabled/default.bak 2>/dev/null || true

ENV PORT=10000
EXPOSE 10000
ENTRYPOINT ["/entrypoint.sh"]
