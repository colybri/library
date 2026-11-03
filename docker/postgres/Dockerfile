FROM postgres:14.24-bookworm AS library_postgres

LABEL org.opencontainers.image.title="library-postgres" \
      org.opencontainers.image.description="PostgreSQL 14 for the Library API, with TLS and file logs" \
      org.opencontainers.image.source="https://github.com/colybri/library"

# openssl generates the server certificate on first start (docker-ssl-entrypoint.sh).
# Log folder (volume postgres_logs): Postgres writes it, Filebeat reads it.
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends openssl; \
	rm -rf /var/lib/apt/lists/*; \
	install -d -o postgres -g postgres -m 750 /var/log/postgresql

COPY --chmod=755 docker-ssl-entrypoint.sh /usr/local/bin/docker-ssl-entrypoint.sh

# Over TCP, so it stays unhealthy while initdb runs the temporary socket-only server
HEALTHCHECK --interval=10s --timeout=5s --retries=5 --start-period=30s \
	CMD pg_isready -q -h 127.0.0.1 -U "${POSTGRES_USER:-postgres}" || exit 1

ENTRYPOINT ["docker-ssl-entrypoint.sh"]
CMD ["postgres"]
