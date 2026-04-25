# ---- Stage 1: Composer ----
FROM composer:2 AS composer

# ---- Stage 2: PHP ----
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html