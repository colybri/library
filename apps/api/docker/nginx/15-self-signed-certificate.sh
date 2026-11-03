#!/bin/sh
# Generates the TLS certificate of nginx on first start when none is mounted
# in /etc/nginx/certificates. It lasts NGINX_SSL_DAYS days (default 825) and is
# regenerated when the container is recreated or the certificate has expired.
set -e

DIR=/etc/nginx/certificates
CERT="$DIR/cert.pem"
KEY="$DIR/key.pem"

if [ -s "$CERT" ] && [ -s "$KEY" ] && openssl x509 -checkend 86400 -noout -in "$CERT" >/dev/null 2>&1; then
	exit 0
fi

if [ -e "$CERT" ] && [ ! -w "$CERT" ]; then
	echo "$0: $CERT is mounted read-only and expires within a day" >&2
	exit 0
fi

openssl req -x509 -nodes -newkey rsa:2048 -days "${NGINX_SSL_DAYS:-825}" \
	-subj "/CN=${NGINX_SSL_CN:-localhost}" \
	-addext "subjectAltName=DNS:${NGINX_SSL_CN:-localhost},DNS:library_nginx,IP:127.0.0.1" \
	-keyout "$KEY" -out "$CERT" 2>/dev/null
chmod 600 "$KEY"
echo "$0: generated a self-signed certificate for ${NGINX_SSL_CN:-localhost}"
