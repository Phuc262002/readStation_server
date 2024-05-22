# Use official PHP image with PHP-FPM
FROM php:8.2-fpm

# Install necessary packages
RUN apt-get update \
    && apt-get install -y \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
        zlib1g-dev \
        libpq-dev \
        libzip-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /datn/readStation_server

# Copy the application files
COPY . .

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set the entry point for the container
CMD ["php", "artisan", "serve", "--host=0.0.0.0"]

# Expose the application port
EXPOSE 8000
