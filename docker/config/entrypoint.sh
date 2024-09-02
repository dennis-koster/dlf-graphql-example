#!/usr/bin/env sh

DOCKER_ROOT_DIRECTORY="${DOCKER_ROOT_DIRECTORY:-/var/www/html/public}"
DOCKER_ROOT_FILE="${DOCKER_ROOT_FILE:-index.php}"
CONTAINER_ROLE=${CONTAINER_ROLE:-webserver}

sed -i -- "s|DOCKER_ROOT_DIRECTORY|$DOCKER_ROOT_DIRECTORY|g" /etc/nginx/nginx.conf
sed -i -- "s|DOCKER_ROOT_FILE|$DOCKER_ROOT_FILE|g" /etc/nginx/nginx.conf

mkdir -p $DOCKER_ROOT_DIRECTORY

if [ ! -f "$DOCKER_ROOT_DIRECTORY/$DOCKER_ROOT_FILE" ]; then
    echo 'It works!' >$DOCKER_ROOT_DIRECTORY/$DOCKER_ROOT_FILE
fi

/usr/bin/supervisord -c /etc/supervisord/conf.d/supervisord.conf
