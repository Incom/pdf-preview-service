#!/usr/bin/env bash

#docker build ./ -f ./docker/fpm/Dockerfile --tag=neomerx/ip2i-service
docker build ./ -f ./docker/fpm.alpine/Dockerfile --tag=neomerx/ip2i-service-alpine

docker build ./ -f ./docker/nginx/Dockerfile --tag=neomerx/ip2i-web
