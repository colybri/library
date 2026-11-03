FROM postgres:14.24-bookworm AS library_postgres

# openssl generates the server certificate on first start (docker-ssl-entrypoint.sh)
RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends openssl; \
	rm -rf /var/lib/apt/lists/*

# Log folder (volume postgres_logs): Postgres writes it, Filebeat reads it
RUN mkdir -p /var/log/postgresql && chown postgres:postgres /var/log/postgresql && chmod 750 /var/log/postgresql

COPY --chmod=755 docker-ssl-entrypoint.sh /usr/local/bin/docker-ssl-entrypoint.sh

ENTRYPOINT ["docker-ssl-entrypoint.sh"]
CMD ["postgres"]
