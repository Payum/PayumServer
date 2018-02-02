FROM php:7.2.1-fpm-alpine

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
    libmcrypt

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
    icu-dev \
    icu \
    php7-soap \
    php7-intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install soap

# install php zip module
RUN apk add \
    --update \
    --no-cache \
    php7-zip \
    libzip \
    zlib-dev \
    && docker-php-ext-install zip

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# install xdebug
RUN apk update \
    && apk add --no-cache git curl openssh-client \
    && apk add --no-cache --virtual build-dependencies \
    && mkdir /xdebug && cd /xdebug && git clone https://github.com/xdebug/xdebug.git \
    && cd xdebug \
    && sh ./rebuild.sh \
    && echo "[xdebug]" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "zend_extension=xdebug.so" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_handler=dbgp" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_mode=req" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=dockerhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && cd  / && rm -fr /xdebug \
    && apk del build-dependencies \
    && rm -rf /tmp/*

#clear cache
RUN rm -rf /var/cache/apk/*

# switch to project dir
WORKDIR /var/www

# create non-root user
RUN adduser -D -u 1000 dev

# login non-root user
USER dev