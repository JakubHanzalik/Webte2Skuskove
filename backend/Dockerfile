FROM php:8.3.0-fpm

# Install PDO extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get update && apt-get install -y git unzip wget fonts-liberation libasound2 libnspr4 libnss3 xdg-utils libatk-bridge2.0-0 libatk1.0-0 libatspi2.0-0 libcairo2 libcups2 libgbm1 libgdk-pixbuf2.0-0 libgtk-3-0 libpango-1.0-0 libpangocairo-1.0-0 libxkbcommon0 libu2f-udev libvulkan1\
    && rm -rf /var/lib/apt/lists/*

RUN apt-get install -y wget

RUN wget -q https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb

RUN apt-get install -y ./google-chrome-stable_current_amd64.deb