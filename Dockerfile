FROM composer:latest as composer

WORKDIR /app

COPY composer.json .
RUN composer install


FROM php:8.0

WORKDIR /app

COPY . .
COPY --from=composer /app/vendor ./vendor



