# PHP 8.4.27 is the first 8.4 release that fixes CVE-2026-109618 and
# CVE-2026-109619 (RCE, 21 Oct 2026). Do not go below it.
ARG PHP_VERSION=8.4.27

# Stages
#   library_php_base    PHP, extensions and runtime config. No code, no dev
#                       tools; shared by production and development.
#   library_php_vendor  build only: Composer installs the production
#                       dependencies, then the code is added and the Symfony
#                       cache is warmed up. Never run.
#   library_php         production: base + the built application, as www-data,
#                       with symfony.prod.ini (OPcache without timestamp checks,
#                       preload). Runs on its own: nothing is mounted.
#   library_php_dev     development (docker compose): base + Composer, Symfony
#                       CLI, git and a coverage driver. The code is mounted, so
#                       editing it never invalidates the image cache.
#
# Layers go from least to most frequently changed. Every download is pinned so
# a build with and without cache produces the same image.
FROM php:${PHP_VERSION}-fpm-alpine AS library_php_base

ENV USER_NAME=www-data
ENV APP_HOME=/srv/app

# Runtime deps: fcgi for the healthcheck, supervisor for the consumers
RUN apk add --no-cache \
		fcgi \
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

RUN set -eux; \
	ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini; \
	mkdir -p $APP_HOME/public $APP_HOME/var /home/$USER_NAME /var/run/php; \
	chown -R $USER_NAME:$USER_NAME $APP_HOME /home/$USER_NAME /var/run/php

# Configuration files change more often than everything above, so they go last.
COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY --chmod=755 docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
# RabbitMQ consumers (api_consumers service): supervisord with one program per subscriber
COPY docker/php/supervisord.conf /etc/supervisord.conf
COPY --chmod=755 docker/php/library-consumers.sh /usr/local/bin/library-consumers
COPY --chmod=755 docker/php/library-consumers-healthcheck.sh /usr/local/bin/library-consumers-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

VOLUME /var/run/php

WORKDIR $APP_HOME

CMD ["php-fpm"]


# Build stage: production dependencies and warmed-up cache.
FROM library_php_base AS library_php_vendor

# Bump together with the dev stage (COPY --from cannot use ARGs)
COPY --from=composer:2.11.1 /usr/bin/composer /usr/bin/composer

ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV COMPOSER_ALLOW_SUPERUSER=1

# Dependencies first: editing the code does not download them again.
COPY composer.json composer.lock symfony.lock ./
RUN --mount=type=cache,target=/root/.composer/cache \
	composer install --no-dev --prefer-dist --no-autoloader --no-scripts --no-progress --no-interaction

COPY . .

# .env is never in the build context (.dockerignore): Symfony falls back to
# .env.dist, and the real values come from the environment at runtime.
RUN set -eux; \
	composer dump-autoload --no-dev --classmap-authoritative --no-interaction; \
	bin/console cache:warmup; \
	bin/console assets:install public --no-interaction; \
	test -f var/cache/prod/Colybri_Library_KernelProdContainer.preload.php; \
	rm -rf var/log/*; \
	rm /usr/bin/composer


# Production image.
FROM library_php_base AS library_php

LABEL org.opencontainers.image.title="library-php" \
      org.opencontainers.image.description="Library API, PHP-FPM production image" \
      org.opencontainers.image.source="https://github.com/colybri/library"

ENV APP_ENV=prod
ENV APP_DEBUG=0

COPY docker/php/conf.d/symfony.prod.ini $PHP_INI_DIR/conf.d/symfony.ini
COPY --from=library_php_vendor --chown=www-data:www-data /srv/app /srv/app

# The master process runs as www-data too (no privileges to drop).
USER www-data

ENTRYPOINT ["docker-php-entrypoint"]
CMD ["php-fpm"]


# Development image: the base plus Composer, the Symfony CLI and a code coverage
# driver, so `make report` works inside the container. It does not contain the
# code (docker compose mounts it), so code changes never rebuild PCOV/Xdebug.
# It runs as root: the entrypoint installs dependencies into the mounted code.
#
#   docker build --target library_php_dev .                              # PCOV (default)
#   docker build --target library_php_dev --build-arg COVERAGE_DRIVER=xdebug .
#   docker build --target library_php_dev --build-arg COVERAGE_DRIVER=none .
FROM library_php_base AS library_php_dev

LABEL org.opencontainers.image.title="library-php-dev" \
      org.opencontainers.image.description="Library API, development image with Composer and a coverage driver" \
      org.opencontainers.image.source="https://github.com/colybri/library"

# acl: setfacl in the entrypoint; git: Composer and GrumPHP
RUN apk add --no-cache \
		acl \
		bash \
		curl \
		file \
		gettext \
		git \
	;

# Bump together with the vendor stage (COPY --from cannot use ARGs)
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

COPY docker/php/conf.d/symfony.dev.ini $PHP_INI_DIR/conf.d/symfony.ini
COPY docker/php/conf.d/coverage.dev.ini $PHP_INI_DIR/conf.d/zz-coverage.ini
COPY --chmod=755 docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint

VOLUME /srv/app/var

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
