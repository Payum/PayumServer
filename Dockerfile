FROM makasim/nginx-php-fpm

MAINTAINER Maksym Kotliar <kotlyar.maksim@gmail.com>

RUN apt-get update && \
    apt-get install -y --no-install-recommends --no-install-suggests openssl pkg-config libssl-dev libsslcommon2-dev && \
    apt-get install -y --no-install-recommends --no-install-suggests php-mongodb php-curl php-intl php-soap && \
    rm -rf /var/lib/apt/lists/*

ENV PAYUM_DEBUG 0
ENV CUSTOM_DIR=/payum/web

EXPOSE 80

ADD . /payum
WORKDIR /payum
