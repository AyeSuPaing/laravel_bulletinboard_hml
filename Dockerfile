# Use PHP 8.2 FPM (matching your likely local environment)
FROM php:8.2-fpm

# Install system dependencies
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
    nginx

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . .

# Set Composer environment variables to prevent memory errors
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy the Nginx config we created earlier
COPY nginx.conf /etc/nginx/sites-available/default

# Fix permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 80
EXPOSE 80

# Start Nginx and PHP-FPM together
CMD service nginx start && php-fpm