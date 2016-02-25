FROM ubuntu:precise

ENV HOME /root
ENV PHP_ERROR_REPORTING E_ALL
ENV PHP_DISPLAY_ERRORS off

MAINTAINER Maksim Kotlyar <kotlyar.maksim@gmail.com>

## libs
RUN set -x && \
    apt-get update && \
    apt-get install -y --no-install-recommends build-essential python-software-properties openssl pkg-config libssl-dev libsslcommon2-dev && \
    add-apt-repository -y ppa:ondrej/php5 && \
    apt-get update && \
    apt-get install -y --no-install-recommends php5 php5-dev php-pear php5-curl php5-intl && \
    pecl install mongodb

RUN a2enmod rewrite
RUN service apache2 stop

RUN echo '\n \
;Added on container build\n \
date.timezone = UTC\n \
extension = mongodb.so\n \
' \
>> /etc/php5/cli/php.ini

RUN echo '\n \
;Added on container build\n \
date.timezone = UTC\n \
extension = mongodb.so\n \
' \
>> /etc/php5/apache2/php.ini

RUN rm -rf /etc/apache2/sites-enabled/*
RUN echo '\n\
\n\
ServerName  0.0.0.0\n\
\n\
<VirtualHost *:80>\n\
    DocumentRoot /app/web\n\
    <Directory /app/web>\n\
        Require all granted\n\
        AllowOverride All\n\
        Order Allow,Deny\n\
        Allow from All\n\
	    DirectoryIndex index.php\n\
    </Directory>\n\
</VirtualHost>\n\
'\
>> /etc/apache2/sites-enabled/payum-server.conf

ADD . /app
WORKDIR /app
CMD docker-entrypoint.sh