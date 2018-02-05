FROM formapro/nginx-php-fpm:latest

MAINTAINER Maksym Kotliar <kotlyar.maksim@gmail.com>

RUN apt-get update && \
    apt-get install -y --no-install-recommends --no-install-suggests openssl pkg-config libssl-dev libsslcommon2-dev && \
    apt-get install -y --no-install-recommends --no-install-suggests php7.1-mongodb php7.1-soap php7.1-xml && \
    rm -rf /var/lib/apt/lists/*

ENV PAYUM_DEBUG 0
ENV NGINX_WEB_ROOT=/payum/public
ENV NGINX_PHP_FALLBACK=/index.php
ENV NGINX_PHP_LOCATION='^/index\.php(/|$)'

EXPOSE 80

ADD . /payum
WORKDIR /payum
