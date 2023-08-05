FROM postgres:14.8-bullseye AS library_postgres


COPY ./ca/server.key /var/lib/postgresql
COPY ./ca/server.crt /var/lib/postgresql

RUN chown root:postgres /var/lib/postgresql/server.key /var/lib/postgresql/server.crt
RUN chmod 640 /var/lib/postgresql/server.key /var/lib/postgresql/server.crt

COPY  postgresql.conf /var/lib/postgresql/data/

