FROM nginx:1.27-alpine

RUN apk add --no-cache curl openssl

COPY public /var/www/public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/entrypoint.sh /usr/local/bin/queue-nginx-entrypoint

RUN chmod +x /usr/local/bin/queue-nginx-entrypoint

ENTRYPOINT ["queue-nginx-entrypoint"]
CMD ["nginx", "-g", "daemon off;"]
