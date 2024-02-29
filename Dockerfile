FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
 && docker-php-ext-install zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html
COPY ./microservice /var/www/html
RUN composer install
