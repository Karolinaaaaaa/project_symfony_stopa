FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
        libzip-dev \
        zip \
 && docker-php-ext-install zip
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN mkdir /var/www/html/vendor \
    && chown -R www-data:www-data /var/www/html/vendor \
    && chmod -R 777 /var/www/html/vendor
WORKDIR /var/www/html
COPY ./microservice /var/www/html
RUN composer install
