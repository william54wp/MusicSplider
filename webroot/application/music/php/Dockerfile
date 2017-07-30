FROM php:fpm-alpine
RUN apk add --no-cache --virtual zlib-dev \
&& docker-php-ext-install pdo_mysql zip