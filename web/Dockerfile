FROM ubuntu:16.04

MAINTAINER Jan Novotny <novotny@profisites.it>

ENV DEBIAN_FRONTEND noninteractive
ENV LANG C.UTF-8

RUN apt-get update && apt-get install -y curl wget gnupg2 apt-transport-https

# Use ondrej ppa since Ubuntu is slow to update to new PHP versions
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys E5267A6C

RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu xenial main" | tee /etc/apt/sources.list.d/ondrej-php.list

RUN apt-get update && apt-get install -y \
    sudo \
    nginx \
    git \
    php7.3 \
    php7.3-cli \
    php7.3-curl \
    php7.3-fpm \
    php7.3-mysql \
    php7.3-intl \
    php7.3-dev \
    php7.3-xml \
    php7.3-mbstring \
    php7.3-zip \
    php7.3-gd \
    php7.3-sqlite


# Composer
RUN curl -so /bin/composer https://getcomposer.org/download/1.8.4/composer.phar
RUN chmod a+rx /bin/composer

RUN yes | pecl install xdebug

#RUN echo '*.*          @logs4.papertrailapp.com:36708' >> /etc/rsyslog.conf

RUN echo 'date.timezone = Europe/Berlin' >> /etc/php/7.3/fpm/conf.d/20-date.ini
RUN echo 'upload_max_filesize = 256M' >> /etc/php/7.3/fpm/conf.d/20-uploads.ini
RUN echo 'post_max_size = 256M' >> /etc/php/7.3/fpm/conf.d/20-uploads.ini

RUN echo "\ndaemon off;\n" >> /etc/nginx/nginx.conf
RUN sed -i "s/user = www-data/user = www-data/g" /etc/php/7.3/fpm/pool.d/www.conf

ADD ./docker/vhost.conf /etc/nginx/sites-enabled/default
ADD ./docker/entrypoint.sh /bin/entrypoint.sh
RUN chmod a+rx /bin/entrypoint.sh

VOLUME ["/var/www", "/var/log/nginx"]

EXPOSE 80
#EXPOSE 80 443

ENTRYPOINT ["bin/entrypoint.sh"]
