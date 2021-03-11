ARG PHP_VERSION
FROM composer:latest as composer

WORKDIR /app

COPY composer.json .
RUN composer install


FROM php:$PHP_VERSION

WORKDIR /app

COPY . .
COPY --from=composer /app/vendor ./vendor



