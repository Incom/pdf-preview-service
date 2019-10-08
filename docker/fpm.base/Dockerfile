FROM php:7.3-fpm-alpine

WORKDIR "/app"

RUN apk update && apk upgrade && apk add \
        autoconf g++ libtool make pcre-dev \
        ghostscript \
        php7-pecl-imagick imagemagick imagemagick-dev \
		php7-gd libjpeg-turbo-dev libpng-dev \
		php7-intl icu-dev \
	&& pecl install imagick \
	&& docker-php-ext-enable imagick \
	&& docker-php-ext-configure gd --with-png-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) \
		gd \
        intl \
    && apk del autoconf g++ libtool make pcre-dev;
