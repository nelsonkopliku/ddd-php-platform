#!/bin/sh

/bin/sh -c "/opt/bin/envsubst < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf"
/bin/sh -c "/opt/bin/envsubst < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf"

exec nginx -g 'daemon off;'
