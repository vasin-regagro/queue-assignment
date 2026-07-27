FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

FROM php:8.3-fpm-alpine

RUN apk add --no-cache bash curl icu-libs libzip mariadb-client \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev linux-headers \
    && docker-php-ext-install pdo_mysql pcntl intl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY docker/php/entrypoint.sh /usr/local/bin/queue-entrypoint

RUN chmod +x /usr/local/bin/queue-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && composer dump-autoload --classmap-authoritative --no-dev --no-interaction

EXPOSE 9000

ENTRYPOINT ["queue-entrypoint"]
CMD ["php-fpm", "-F"]
