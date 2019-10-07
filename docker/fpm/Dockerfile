FROM php:7.3-fpm

COPY public /app/public/
COPY server /app/server/
COPY vendor /app/vendor/
COPY .env   /app/.env

WORKDIR "/app"

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        ghostscript \
		libmagickwand-dev \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
	&& pecl install imagick \
	&& docker-php-ext-enable imagick \
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
#	&& docker-php-ext-configure pdo_mysql --with-pdo-mysql \
	&& docker-php-ext-install -j$(nproc) \
		gd \
        intl \
#        gettext \
#        pdo_mysql \
#        zip \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
    && chmod a+r -R /app && find /app -perm /u+x -execdir chmod a+x {} \;
