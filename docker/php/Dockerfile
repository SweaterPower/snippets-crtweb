FROM php:7.2-fpm-alpine

RUN set -xe && apk update && apk upgrade

RUN apk add --no-cache \
    shadow \
    libzip \
    libintl \
    icu \
    icu-dev \
    bash \
    curl \
    libmcrypt \
    libmcrypt-dev \
    libxml2-dev \
    freetype \
    freetype-dev \
    libpng \
    libpng-dev \
    libjpeg-turbo \
    libjpeg-turbo-dev \
    postgresql-dev \
    pcre-dev \
    git \
    g++ \
    make \
    autoconf

RUN  docker-php-ext-configure gd \
    --with-gd \
    --with-freetype-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ && \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j${NPROC} gd && \
  apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

RUN docker-php-ext-install intl opcache pdo_pgsql zip pcntl sockets exif

RUN docker-php-source extract \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete


# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# change uid for nginx user to avoid problems with host permissions
ARG HOST_USER_ID
ARG HOST_GROUP_ID
RUN if [ ! -z "$HOST_USER_ID" ] ; then usermod -u $HOST_USER_ID www-data ; fi
RUN if [ ! -z "$HOST_GROUP_ID" ] ; then groupmod -g $HOST_GROUP_ID www-data ; fi
RUN mkdir /var/session && chown www-data:www-data /var/session


# symfony pool config
COPY symfony.conf /usr/local/etc/php-fpm.d/symfony.conf


WORKDIR /var/www
