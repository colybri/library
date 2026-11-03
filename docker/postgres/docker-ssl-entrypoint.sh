#!/bin/sh
# Generates the self-signed TLS certificate of the server on first start, so
# no private key lives in git or in an image layer. It stays in the container
# until it is recreated; then a new one is generated.
set -e

SSL_DIR=/var/lib/postgresql/ssl

if [ ! -s "$SSL_DIR/server.key" ] || [ ! -s "$SSL_DIR/server.crt" ]; then
	mkdir -p "$SSL_DIR"
	openssl req -x509 -nodes -newkey rsa:2048 -days "${POSTGRES_SSL_DAYS:-825}" \
		-subj "/CN=${POSTGRES_SSL_CN:-postgres}" \
		-keyout "$SSL_DIR/server.key" -out "$SSL_DIR/server.crt" 2>/dev/null
	chown -R postgres:postgres "$SSL_DIR"
	chmod 700 "$SSL_DIR"
	chmod 600 "$SSL_DIR/server.key"
	chmod 644 "$SSL_DIR/server.crt"
	echo "Generated a self-signed TLS certificate in $SSL_DIR"
fi

exec docker-entrypoint.sh "$@"
