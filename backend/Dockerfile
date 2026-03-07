FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip mbstring exif pcntl bcmath \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN cp .env.example .env || true
RUN composer install --no-dev --optimize-autoloader

RUN mkdir -p storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan migrate --force && php artisan db:seed --force && php -S 0.0.0.0:${PORT:-8080} -t public"]