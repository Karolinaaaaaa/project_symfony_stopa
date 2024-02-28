FROM php:8.0-fpm
RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
 && docker-php-ext-install zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install
WORKDIR /app
