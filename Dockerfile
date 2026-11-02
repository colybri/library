# PHP 8.4.27 is the first 8.4 release that fixes CVE-2026-109618 and
# CVE-2026-109619 (RCE, 21 Oct 2026). Do not go below it.
ARG PHP_VERSION=8.4.27

FROM php:${PHP_VERSION}-fpm-alpine AS library_php
# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact

ARG UID
ARG GID

ENV USER_NAME=www-data
ENV APP_HOME /srv/app

# persistent / runtime deps
RUN apk add --no-cache \
		acl \
        curl \
		fcgi \
		file \
		gettext \
		git \
		gnu-libiconv \
        bash \
	;

# install gnu-libiconv and set LD_PRELOAD env to make iconv work fully on Alpine image.
# see https://github.com/docker-library/php/issues/240#issuecomment-763112749
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so

ARG APCU_VERSION=5.1.24
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
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql ;\
	docker-php-ext-install -j$(nproc) \
		intl \
		zip \
        pdo \
        pdo_pgsql \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
        amqp \
        redis \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		opcache \
        amqp \
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
	apk del .build-deps \
    ;

# Workaround for rabbitmq linking issue
RUN ln -s /usr/lib /usr/local/lib64

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/symfony.dev.ini $PHP_INI_DIR/conf.d/symfony.ini

COPY docker/php/php-fpm.d/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

VOLUME /var/run/php


# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1

ENV PATH="${PATH}:/root/.composer/vendor/bin"

RUN wget https://get.symfony.com/cli/installer -O - | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

RUN export PATH="$HOME/.symfony/bin:$PATH"

RUN mkdir -p $APP_HOME/public
RUN mkdir -p /home/$USER_NAME
RUN chown -R $USER_NAME:$USER_NAME $APP_HOME
RUN chown $USER_NAME:$USER_NAME /home/$USER_NAME

WORKDIR $APP_HOME


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


ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


# Development image: same as library_php plus a code coverage driver, so
# `make report` works inside the container. Production keeps using the
# library_php target and never ships PCOV or Xdebug.
#
#   docker build --target library_php_dev .                              # PCOV (default)
#   docker build --target library_php_dev --build-arg COVERAGE_DRIVER=xdebug .
#   docker build --target library_php_dev --build-arg COVERAGE_DRIVER=none .
FROM library_php AS library_php_dev

ARG COVERAGE_DRIVER=pcov
ARG PCOV_VERSION=1.0.12

RUN set -eux; \
	apk add --no-cache --virtual .coverage-build-deps $PHPIZE_DEPS linux-headers; \
	case "$COVERAGE_DRIVER" in \
		pcov) pecl install pcov-${PCOV_VERSION}; docker-php-ext-enable pcov ;; \
		xdebug) pecl install xdebug; docker-php-ext-enable xdebug ;; \
		none) ;; \
		*) echo "Unknown COVERAGE_DRIVER '$COVERAGE_DRIVER' (use pcov, xdebug or none)" >&2; exit 1 ;; \
	esac; \
	pecl clear-cache; \
	apk del .coverage-build-deps

COPY docker/php/conf.d/coverage.dev.ini $PHP_INI_DIR/conf.d/zz-coverage.ini
