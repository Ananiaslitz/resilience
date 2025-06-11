FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y libpq-dev unzip git \
    && docker-php-ext-install pcntl

RUN apt-get install -y \
    git \
    unzip \
    libzip-dev \
    gcc \
    make \
    autoconf \
    pkg-config \
    zlib1g-dev \
    libssl-dev \
    libpcre3-dev \
    && pecl install pcov \
    && echo "extension=pcov.so" > /usr/local/etc/php/conf.d/pcov.ini \
    && echo "pcov.enabled=1" >> /usr/local/etc/php/conf.d/pcov.ini

WORKDIR /app
COPY . /app
