FROM arm64v8/php:8.1-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-install zip pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader