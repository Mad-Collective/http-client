FROM splitbrain/phpfarm:latest

WORKDIR /app/http-client
VOLUME ["/app/http-client"]

RUN curl -sS https://getcomposer.org/installer | php-7.1 && mv /app/http-client/composer.phar /usr/local/bin/composer
