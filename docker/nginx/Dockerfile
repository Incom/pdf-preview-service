FROM nginx:alpine

COPY public /app/public/
COPY server /app/server/
COPY vendor /app/vendor/
COPY .env   /app/.env

WORKDIR "/app"

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
	&& ln -sf /dev/stderr /var/log/nginx/error.log
