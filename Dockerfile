FROM makasim/nginx-php-fpm

RUN apt-get update && \
    apt-get install -y --no-install-recommends --no-install-suggests openssl pkg-config libssl-dev libsslcommon2-dev && \
    apt-get install -y --no-install-recommends --no-install-suggests php-mongodb php-curl php-intl php-soap && \
    rm -rf /var/lib/apt/lists/*

