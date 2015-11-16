FROM ubuntu:precise

ENV HOME /root

MAINTAINER Maksim Kotlyar <kotlyar.maksim@gmail.com>

## libs
RUN apt-get update
RUN apt-get install -y build-essential python-software-properties

RUN apt-get update

## php
RUN add-apt-repository -y ppa:ondrej/php5
RUN apt-get update
RUN apt-get install -y php5

## php config
#RUN sed -i "s/;date.timezone =.*/date.timezone = UTC/" /etc/php5/apache2/php.ini
#RUN sed -i "s/;date.timezone =.*/date.timezone = UTC/" /etc/php5/cli/php.ini

## php libs
RUN apt-get install -y php5-curl php5-intl
RUN apt-get install -y php5-mongo
RUN apt-get install -y php5-fpm

RUN a2enmod rewrite
RUN service apache2 stop

RUN echo '\n\
;Added on container build\n\
date.timezone = UTC\n'\
>> /etc/php5/cli/php.ini

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
</VirtualHost>\n'\
>> /etc/apache2/sites-enabled/payum-server.conf

ADD . /app
WORKDIR /app

# docker run -it -p 80:80 payum apachectl -e info -DFOREGROUND