# Start from the official PHP-FPM Alpine image
FROM php:8.2-fpm-alpine

# Install system dependencies (git, packages for PHP extensions)
RUN apk add --no-cache \
    autoconf \
    g++ \
    make \
    git \
    bash \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    icu-dev \
    oniguruma-dev \
    mariadb-client \
    mysql-dev

# Install required PHP extensions for Symfony
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Default command
CMD ["php-fpm"]
