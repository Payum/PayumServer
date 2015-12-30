FROM php:5.6-apache

MAINTAINER Maksim Kotlyar <kotlyar.maksim@gmail.com>

RUN set -x && \
    apt-get update && \
    apt-get install -y openssl libssl-dev libsasl2-dev libicu-dev php5-dev && \
    docker-php-ext-install intl && \
    pecl install mongodb && \
    a2enmod rewrite && \
    rm -rf /var/lib/apt/lists/* && \
    mkdir -p /app/web && \
    rm -rf /var/www/html && \
    ln -s /app/web /var/www/html

COPY . /app/
COPY config/php.ini /usr/local/etc/php/

#RUN chown -R www-data:www-data /app

WORKDIR /app/