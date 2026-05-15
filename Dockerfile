FROM php:8.3-fpm

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    libmariadb-dev \
    nginx

# 2. Install AND Enable PHP extensions
# We add 'pdo' and 'pdo_mysql' explicitly
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && docker-php-ext-enable pdo_mysql pdo_pgsql

# 3. Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# 4. Install dependencies
# We add --no-scripts to prevent Laravel from running artisan commands 
# BEFORE the environment variables (like DB_HOST) are actually available.
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY nginx.conf /etc/nginx/sites-available/default
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

CMD service nginx start && php-fpm