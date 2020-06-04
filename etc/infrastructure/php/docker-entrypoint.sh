#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
    APP_PATH="/srv/app"
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

	# fixes permission issues

    mkdir -p /srv/app/var/cache /srv/app/var/log
    # Always return true because mac doesn't support some flags
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX /srv/app/var || true
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX /srv/app/var || true

	if [ "$APP_ENV" != 'prod' ]; then
		cd ${APP_PATH} && composer install --prefer-dist --no-progress --no-suggest --no-interaction
	fi

	echo "Waiting for db to be ready..."
	until ${APP_PATH}/bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
		sleep 1
	done

	${APP_PATH}/bin/console messenger:setup-transports failed

    # is it ok to have migration re-run at deploy?
	/srv/etc/infrastructure/php/scripts/run-migrations.sh $APP_ENV

#	export APP_SECRET=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 64 | head -n 1)

fi

exec docker-php-entrypoint "$@"
