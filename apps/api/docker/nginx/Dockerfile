FROM nginx:1.30.6-alpine AS webserver

LABEL org.opencontainers.image.title="library-nginx" \
      org.opencontainers.image.description="nginx in front of the Library API PHP-FPM" \
      org.opencontainers.image.source="https://github.com/colybri/library"

# openssl generates the self-signed certificate on start (docker-entrypoint.d)
RUN set -eux; \
	apk add --no-cache openssl; \
	ln -sf /dev/stdout /var/log/nginx/access.log; \
	ln -sf /dev/stderr /var/log/nginx/error.log; \
	rm -rf /etc/nginx/conf.d/*; \
	mkdir -p /var/log/library-nginx /etc/nginx/certificates

# Runs before nginx starts (official image entrypoint): creates a self-signed
# certificate unless one is mounted in /etc/nginx/certificates. Nothing secret
# is baked into the image, and a cached layer never serves an expired one.
COPY --chmod=755 15-self-signed-certificate.sh /docker-entrypoint.d/15-self-signed-certificate.sh

COPY nginx.conf /etc/nginx/conf.d/default.conf

# stub_status on the internal port 8080 (only reachable from private networks)
HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
	CMD wget -q -O /dev/null http://127.0.0.1:8080/nginx-status || exit 1
