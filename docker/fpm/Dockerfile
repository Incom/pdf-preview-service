FROM neomerx/ip2i-fpm-base

COPY public                             /app/public/
COPY server                             /app/server/
COPY vendor                             /app/vendor/
COPY .env                               /app/.env
COPY docker/fpm/policy.xml              /etc/ImageMagick-7/policy.xml
COPY docker/fpm/file_upload_config.ini  /usr/local/etc/php/conf.d/file_upload_config.ini

WORKDIR "/app"

RUN chmod a+r -R /app;
