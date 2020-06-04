ARG PHP_VERSION=7.4

FROM php:${PHP_VERSION}-fpm-alpine AS php_build

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		fcgi \
		file \
		gettext \
		git \
		make \
	;

ARG APCU_VERSION=5.1.18
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libzip-dev \
		zlib-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-install -j$(nproc) \
		intl \
		pdo_mysql \
		zip \
		sockets \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .api-phpexts-rundeps $runDeps; \
	\
	apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY etc/infrastructure/php/conf.d/app.prod.ini $PHP_INI_DIR/conf.d/app.ini

RUN set -eux; \
	{ \
		echo '[www]'; \
		echo 'ping.path = /ping'; \
	} | tee /usr/local/etc/php-fpm.d/docker-healthcheck.conf

# Workaround to allow using PHPUnit 8 with Symfony 4.3
ENV SYMFONY_PHPUNIT_VERSION=8.3

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
RUN set -eux; \
	composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
	composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv

# build for production
ARG APP_ENV=prod

# copy only specifically what we need
COPY app/bin app/bin
COPY app/config app/config
COPY app/public app/public
COPY app/src app/src
COPY app/templates app/templates
COPY app/.env app/.env
COPY app/.env.test app/.env.test
COPY app/composer.json app/composer.lock app/symfony.lock app/

COPY etc etc/

COPY src src/

COPY Makefile Makefile

RUN set -eux; \
	composer install -d app/ --prefer-dist --no-dev --no-scripts --no-progress --no-suggest; \
	composer clear-cache

RUN composer dump-env -d app/ prod

RUN set -eux; \
	mkdir -p app/var/cache app/var/log; \
	composer dump-autoload -d app/ --classmap-authoritative --no-dev; \
	composer run-script -d app/ --no-dev post-install-cmd; \
	chmod +x app/bin/console; sync

VOLUME /srv/app/var

COPY etc/infrastructure/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY etc/infrastructure/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

FROM php_build as build_debug

ARG XDEBUG_VERSION=2.9.1
RUN set -eux; \
	apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
	pecl install xdebug-$XDEBUG_VERSION; \
	docker-php-ext-enable xdebug; \
	apk del .build-deps
