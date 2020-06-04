ARG NGINX_VERSION=1.17

FROM nginx:${NGINX_VERSION}-alpine

RUN apk add --no-cache \
       curl \
    && mkdir /opt/bin \
    && curl -L https://github.com/a8m/envsubst/releases/download/v1.1.0/envsubst-`uname -s`-`uname -m` -o /opt/bin/envsubst \
    && chmod +x /opt/bin/envsubst

COPY etc/infrastructure/nginx/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

COPY etc/infrastructure/nginx/nginx.conf.template /etc/nginx/nginx.conf.template
COPY etc/infrastructure/nginx/conf.d/default.conf.template /etc/nginx/conf.d/default.conf.template
COPY app/public /srv/app/public/

ENTRYPOINT ["docker-entrypoint"]

WORKDIR /srv/app/public
