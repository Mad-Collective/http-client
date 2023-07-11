FROM php:8.0-fpm-alpine

WORKDIR /app/http-client
VOLUME ["/app/http-client"]

RUN apk add --no-cache \
        curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer
