FROM splitbrain/phpfarm:latest

WORKDIR /app/http-client
VOLUME ["/app/http-client"]

RUN curl -sS https://getcomposer.org/installer | php-5.5 && mv /app/http-client/composer.phar /usr/local/bin/composer
