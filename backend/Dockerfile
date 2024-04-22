FROM php:8.3.0-fpm

# Install PDO extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Update and install system packages
RUN apt-get update && apt-get install -y git unzip \
    && rm -rf /var/lib/apt/lists/*
