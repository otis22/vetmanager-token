ARG VERSION

FROM php:${VERSION}-cli-alpine

WORKDIR /app

RUN apk add --update linux-headers \
    && apk --no-cache add  icu-libs \
    && if php -r 'exit(PHP_VERSION_ID < 70400 ? 0 : 1);'; then :; else apk --no-cache add icu-data-full; fi \
    && apk --no-cache add icu-dev \
    && apk --no-cache add pcre-dev ${PHPIZE_DEPS} \
    && if php -r 'exit(PHP_VERSION_ID < 80000 ? 0 : 1);'; then pecl install xdebug-3.1.6; else pecl install xdebug; fi \
    && apk del pcre-dev ${PHPIZE_DEPS}

RUN docker-php-ext-install -j$(nproc) bcmath intl

RUN curl -sS https://getcomposer.org/installer | \
    php -- --2 --install-dir=/usr/local/bin --filename=composer
