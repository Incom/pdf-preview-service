#!/usr/bin/env bash

docker build ./ -f ./docker/fpm/Dockerfile --tag=neomerx/ip2i-service
docker build ./ -f ./docker/nginx/Dockerfile --tag=neomerx/ip2i-web

docker push neomerx/ip2i-service
docker push neomerx/ip2i-web
