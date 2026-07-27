#!/usr/bin/env sh
set -eu

mkdir -p /etc/nginx/certs

if [ ! -s /etc/nginx/certs/server.crt ] || [ ! -s /etc/nginx/certs/server.key ]; then
    openssl req -x509 -nodes -newkey rsa:2048 -days 3650 \
        -keyout /etc/nginx/certs/server.key \
        -out /etc/nginx/certs/server.crt \
        -subj "/CN=localhost" \
        -addext "subjectAltName=DNS:localhost,IP:127.0.0.1"
fi

exec /docker-entrypoint.sh "$@"
