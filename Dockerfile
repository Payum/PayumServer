FROM ubuntu:15.10

MAINTAINER Maksim Kotlyar <kotlyar.maksim@gmail.com>

RUN set -x && \
    apt-get update && \
    apt-get install -y --no-install-recommends pkg-config openssl libxml2 libssl-dev libsasl2-dev libicu-dev php-soap php5-dev php5-fpm nginx && \
    pecl install mongodb && \
    rm -rf /etc/nginx/sites-enabled/*

COPY . /app/
COPY config/php.ini /etc/php5/cli/conf.d/05-payum-server.ini
COPY config/php.ini /etc/php5/fpm/conf.d/05-payum-server.ini
COPY config/php-fpm.conf /etc/php5/fpm/php-fpm.conf
COPY config/payum-server-nginx /etc/nginx/sites-enabled/payum-server

CMD "php5-fpm"

EXPOSE 80