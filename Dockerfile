FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
COPY src ./src
RUN composer install --no-dev --no-interaction --optimize-autoloader

FROM php:8.5-cli
WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY composer.json composer.lock ./
COPY bin ./bin
COPY src ./src
RUN chmod +x bin/laravel-ready
ENTRYPOINT ["php", "bin/laravel-ready"]
