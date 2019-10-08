#!/usr/bin/env bash

if [ -n "$1" ]; then
    docker build ./ -f ./docker/fpm.base//Dockerfile --tag=neomerx/ip2i-fpm-base
    docker push neomerx/ip2i-fpm-base
fi

docker build ./ -f ./docker/fpm/Dockerfile --tag=neomerx/ip2i-service
docker build ./ -f ./docker/nginx/Dockerfile --tag=neomerx/ip2i-web
