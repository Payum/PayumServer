FROM php:7.2-fpm-alpine

# install php dependencies
RUN apk add \
    --update \
    --no-cache \
    --virtual \
    .ext-deps \
    libjpeg-turbo-dev \
    libwebp-dev \
    libpng-dev \
    openssl-dev \
    freetype-dev \
    libmcrypt-dev \
    autoconf \
    g++ \
    make \
    libssh2 \
    libpng \
    freetype \
    libjpeg-turbo \
    libgcc \
    libxml2-dev \
    libstdc++ \
    icu-libs \
    libltdl \
    libmcrypt \
    icu-dev \
    icu

# install php mongodb dependencies
RUN apk add \
    --no-cache \
    --virtual \
    .mongodb-ext-build-deps \
    pcre-dev

# install php mongodb module
RUN pecl install mongodb \
    && apk del .mongodb-ext-build-deps \
    && pecl clear-cache \
    && docker-php-ext-enable mongodb \
    && docker-php-source delete

# install php modules
RUN apk add \
    --update \
    --no-cache \
    php7-soap \
    php7-intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install soap

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

#clear cache
RUN rm -rf /var/cache/apk/*

# switch to project dir
WORKDIR /var/www

# create non-root user
RUN adduser -D -u 1000 dev

# login non-root user
USER dev