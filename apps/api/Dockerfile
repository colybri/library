# PHP 8.4.27 is the first 8.4 release that fixes CVE-2026-109618 and
# CVE-2026-109619 (RCE, 21 Oct 2026). Do not go below it.
ARG PHP_VERSION=8.4.27

# Stages
#   library_php_base  PHP, extensions and tools. No application code, so it is
#                     only rebuilt when this file or docker/php changes.
#   library_php       production: base + the application code.
#   library_php_dev   development (docker-compose): base + a coverage driver.
#                     The code is mounted as a volume, so it is not copied and
#                     editing it never invalidates the image cache.
#
# Layers go from least to most frequently changed. Every download is pinned so
# a build with and without cache produces the same image.
FROM php:${PHP_VERSION}-fpm-alpine AS library_php_base

ENV USER_NAME=www-data
ENV APP_HOME=/srv/app

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
		bash \
		curl \
		fcgi \
		file \
		gettext \
		git \
		gnu-libiconv \
		supervisor \
	;

# install gnu-libiconv and set LD_PRELOAD env to make iconv work fully on Alpine image.
# see https://github.com/docker-library/php/issues/240#issuecomment-763112749
ENV LD_PRELOAD=/usr/lib/preloadable_libiconv.so

ARG APCU_VERSION=5.1.24
ARG AMQP_VERSION=2.2.0
ARG REDIS_VERSION=6.3.0
# OpenTelemetry auto-instrumentation hooks (traces sent to the APM Server)
ARG OPENTELEMETRY_VERSION=1.4.2
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		icu-dev \
		libpq-dev \
		libzip-dev \
		zlib-dev \
		postgresql-dev \
		rabbitmq-c-dev \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql; \
	docker-php-ext-install -j$(nproc) \
		intl \
		zip \
		pdo \
		pdo_pgsql \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
		amqp-${AMQP_VERSION} \
		redis-${REDIS_VERSION} \
		opentelemetry-${OPENTELEMETRY_VERSION} \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
		amqp \
		redis \
		opentelemetry \
	; \
	\
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
	\
	apk del .build-deps; \
	# Workaround for rabbitmq linking issue
	ln -s /usr/lib /usr/local/lib64

# Composer
# Bump together with the version in this tag (COPY --from cannot use ARGs)
COPY --from=composer:2.11.1 /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# Symfony CLI (used by `make security`), pinned instead of the moving installer script
ARG SYMFONY_CLI_VERSION=5.22.2
RUN set -eux; \
	case "$(apk --print-arch)" in \
		x86_64) arch=amd64 ;; \
		aarch64) arch=arm64 ;; \
		*) echo "Unsupported architecture $(apk --print-arch)" >&2; exit 1 ;; \
	esac; \
	curl -fsSL "https://github.com/symfony-cli/symfony-cli/releases/download/v${SYMFONY_CLI_VERSION}/symfony-cli_linux_${arch}.tar.gz" \
		| tar -xz -C /usr/local/bin symfony; \
	symfony version

RUN set -eux; \
	ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini; \
	mkdir -p $APP_HOME/public /home/$USER_NAME; \
	chown -R $USER_NAME:$USER_NAME $APP_HOME /home/$USER_NAME

# Configuration files change more often than everything above, so they go last.
COPY docker/php/conf.d/symfony.dev.ini $PHP_INI_DIR/conf.d/symfony.ini
COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY --chmod=755 docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
COPY --chmod=755 docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
# RabbitMQ consumers (api_consumers service): supervisord with one program per subscriber
COPY docker/php/supervisord.conf /etc/supervisord.conf
COPY --chmod=755 docker/php/library-consumers.sh /usr/local/bin/library-consumers
COPY --chmod=755 docker/php/library-consumers-healthcheck.sh /usr/local/bin/library-consumers-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

VOLUME /var/run/php

WORKDIR $APP_HOME

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


# Production image: the only stage that copies the application code.
FROM library_php_base AS library_php

COPY --chown=$USER_NAME:$USER_NAME . .

RUN set -eux; \
	#mkdir -p var/cache var/log; \
	#composer install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
	#composer dump-autoload --classmap-authoritative --no-dev; \
	#composer symfony:dump-env prod; \
	#composer run-script --no-dev post-install-cmd; \
	chmod +x bin/console; sync \
	;

VOLUME /srv/app/var


# Development image: the base plus a code coverage driver, so `make report`
# works inside the container. It does not contain the code (docker-compose
# mounts it), so code changes never rebuild PCOV/Xdebug. Production keeps
# using the library_php target and never ships PCOV or Xdebug.
#
#   docker build --target library_php_dev .                              # PCOV (default)
#   docker build --target library_php_dev --build-arg COVERAGE_DRIVER=xdebug .
#   docker build --target library_php_dev --build-arg COVERAGE_DRIVER=none .
FROM library_php_base AS library_php_dev

ARG COVERAGE_DRIVER=pcov
ARG PCOV_VERSION=1.0.12
ARG XDEBUG_VERSION=3.5.3

RUN set -eux; \
	apk add --no-cache --virtual .coverage-build-deps $PHPIZE_DEPS linux-headers; \
	case "$COVERAGE_DRIVER" in \
		pcov) pecl install pcov-${PCOV_VERSION}; docker-php-ext-enable pcov ;; \
		xdebug) pecl install xdebug-${XDEBUG_VERSION}; docker-php-ext-enable xdebug ;; \
		none) ;; \
		*) echo "Unknown COVERAGE_DRIVER '$COVERAGE_DRIVER' (use pcov, xdebug or none)" >&2; exit 1 ;; \
	esac; \
	pecl clear-cache; \
	apk del .coverage-build-deps

COPY docker/php/conf.d/coverage.dev.ini $PHP_INI_DIR/conf.d/zz-coverage.ini
