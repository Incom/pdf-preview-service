FROM php:7.3-fpm

WORKDIR "/app"

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        ghostscript \
        libmagickwand-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
	&& pecl install imagick \
	&& docker-php-ext-enable imagick \
	&& docker-php-ext-configure gd --with-png-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) \
		gd \
        intl \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*;
