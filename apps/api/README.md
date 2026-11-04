# Library API

API REST para gestionar un catálogo bibliográfico: autores, libros, ediciones, editoriales y países. Está construida con Symfony 7.4 sobre PHP 8.4, sigue una arquitectura hexagonal con CQRS y eventos de dominio, persiste en PostgreSQL mediante Doctrine DBAL y publica sus eventos en RabbitMQ.

Es la app `apps/api` del monorepo. El arranque con Docker, la infraestructura compartida y la CI se describen en el [README de la raíz](../../README.md). Salvo que se diga lo contrario, las rutas de este documento son relativas a `apps/api/`.

## Descripción funcional

La aplicación expone una API JSON bajo el prefijo `/api/v1` pensada para ser consumida por un cliente (frontend u otro servicio). Permite:

- Dar de alta, consultar, modificar y borrar los recursos del catálogo.
- Buscar autores, editoriales y países por palabras clave con paginación (`keywords`, `offset`, `limit`).
- Buscar ediciones en la API de Google Books por título, autor, editorial o ISBN (`/api/v1/edition/seek`).
- Adjuntar ficheros a una edición (PDF, EPUB, JPEG, PNG, WebP o texto, hasta 200 MB), listarlos, descargarlos y borrarlos. Se guardan en S3 (SeaweedFS) y solo se descargan a través de la API.
- Autenticarse con email y contraseña para obtener un token JWT.

Cada caso de uso es un comando o una consulta que se despacha por un bus de Symfony Messenger (`command.bus`, `query.bus`). Los agregados emiten eventos de dominio (por ejemplo `AuthorCreated`) que se publican en un exchange de RabbitMQ; si RabbitMQ no está disponible, el evento se guarda en PostgreSQL como respaldo. Los suscriptores de dominio consumen esos eventos mediante comandos de consola.

## Funcionalidades principales

- CRUD de autores, libros, ediciones y editoriales; consulta de países.
- Búsqueda paginada de autores (por nombre y apellidos), editoriales y países.
- Búsqueda de ediciones en Google Books (`src/Infrastructure/Persistence/Api/Google/`).
- Autenticación JWT con [LexikJWTAuthenticationBundle](config/packages/lexik_jwt_authentication.yaml) y usuarios guardados en base de datos.
- Conexiones de lectura y escritura separadas a PostgreSQL ([config/context/connections.yaml](config/context/connections.yaml)).
- Bus de eventos sobre RabbitMQ con respaldo en PostgreSQL y consumidores por cola ([config/context/message-broker.yaml](config/context/message-broker.yaml)).
- Comandos de consola para crear usuarios, cargar fixtures y configurar o consumir colas.

## Arquitectura

```mermaid
flowchart LR
    client["Cliente HTTP<br/>(frontend, Behat, curl)"]

    subgraph docker["docker compose (proyecto library)"]
        nginx["api_nginx<br/>Nginx :80 / :443"]
        php["api<br/>PHP-FPM 8.4 + Symfony 7.4"]
        consumers["api_consumers<br/>supervisord + library:rabbit:consume"]
        postgres[("postgres<br/>PostgreSQL 14")]
        rabbit[["rabbitmq<br/>RabbitMQ"]]
        redis[("redis<br/>Redis")]
        pgadmin["pgadmin<br/>pgAdmin"]
    end

    google["Google Books API<br/>googleapis.com/books/v1"]

    client -->|"HTTPS /api/v1 + JWT"| nginx
    nginx -->|"FastCGI (socket)"| php
    php -->|"DBAL lectura / escritura"| postgres
    php -->|"Publica eventos de dominio"| rabbit
    php -.->|"Respaldo si RabbitMQ falla"| postgres
    php -->|"Sesiones"| redis
    php -->|"HTTP (Guzzle)"| google
    rabbit -->|"Colas por suscriptor"| consumers
    postgres -.->|"Eventos de respaldo"| consumers
    consumers --> postgres
    consumers -->|"Mensajes ya procesados"| redis
    pgadmin --> postgres
```

- **Nginx** (`api_nginx`) recibe las peticiones y las pasa a PHP-FPM por un socket compartido (volumen `php_socket`). Genera un certificado autofirmado al arrancar, si no hay uno montado en `/etc/nginx/certificates` ([docker/nginx/15-self-signed-certificate.sh](docker/nginx/15-self-signed-certificate.sh)).
- **Symfony** (`api`) resuelve la ruta, valida el JWT y despacha el comando o la consulta. Los controladores están en `src/Entrypoint/Controller/`, los casos de uso en `src/Application/` y el modelo en `src/Domain/`.
- **PostgreSQL** guarda los datos del catálogo, los usuarios y los eventos de respaldo. No se usa el ORM, solo DBAL.
- **RabbitMQ** recibe los eventos de dominio en el exchange definido por `RABBITMQ_EXCHANGE`. `library:rabbit:configure` crea las colas de cada suscriptor y `library:rabbit:consume` procesa cada una.
- **Consumidores** (`api_consumers`) usa la misma imagen y el mismo código que `api`, pero en lugar de PHP-FPM ejecuta supervisord con un programa `library:rabbit:consume` por suscriptor. Ver [Consumidores de RabbitMQ](#consumidores-de-rabbitmq).
- **Redis** es el almacén de sesiones de Symfony ([config/packages/framework.yaml](config/packages/framework.yaml)) y guarda qué suscriptor ya procesó cada mensaje, para no procesar dos veces un evento que RabbitMQ entregue de nuevo (`RedisProcessorRepository`, claves `library:processed:*` que caducan a los 7 días).
- **Google Books** es la única integración externa: se usa para buscar ediciones. `GOOGLE_BOOKS_API_KEY` es opcional.

Los servicios `api`, `api_consumers` y `api_nginx` se definen en [compose.api.yml](compose.api.yml); PostgreSQL, RabbitMQ, Redis y pgAdmin son infraestructura compartida en [docker/compose.infra.yml](../../docker/compose.infra.yml).

## Tecnologías principales

| Área | Tecnología |
|---|---|
| Lenguaje | PHP 8.4 (mínimo 8.4.27, fijado en [composer.json](composer.json) y [Dockerfile](Dockerfile)) |
| Framework | Symfony 7.4 LTS, Symfony Messenger |
| Persistencia | PostgreSQL 14, Doctrine DBAL 4, [colybri/criteria](https://github.com/colybri/criteria) |
| Mensajería | RabbitMQ (extensión `amqp`), bus de respaldo en PostgreSQL |
| DDD y logging | `pccomponentes/ddd`, `pccomponentes/ddd-logging`, `pccomponentes/messenger-bundle`, Monolog |
| Autenticación | `lexik/jwt-authentication-bundle` 3 |
| HTTP saliente | Guzzle 7 |
| Pruebas y calidad | PHPUnit 11.5, Behat 3, PHPStan 2, PHP_CodeSniffer, GrumPHP 2, PhpMetrics |
| Contenedores | Docker, Docker Compose, Nginx |

## Configuración

Symfony lee las variables de `apps/api/.env` (está en `.gitignore`). Si no existe, usa [.env.dist](.env.dist), que contiene todas las variables con valores por defecto pensados para la CI. Este fichero es distinto del `.env` de la raíz, que solo usa Docker Compose.

```bash
cp apps/api/.env.dist apps/api/.env     # desde la raíz del repositorio
```

Después edita `apps/api/.env` con tus valores. Las variables más importantes:

| Variable | Obligatoria | Descripción | Valor en `.env.dist` |
|---|:---:|---|---|
| `APP_ENV` | Sí | Entorno de Symfony (`dev`, `test`, `prod`) | `dev` |
| `APP_SECRET` | Sí | Secreto de Symfony | `ChangeMe` |
| `DATABASE_URL` | Sí | DSN de PostgreSQL que usa DBAL. Con Docker el host es `postgres` | `postgresql://library:library@127.0.0.1:5432/library…` |
| `POSTGRES_WRITE_HOST` / `POSTGRES_READ_HOST` | Sí | Host de PostgreSQL para escritura y lectura. Con Docker usa `postgres` | `127.0.0.1` |
| `POSTGRES_WRITE_DB` / `POSTGRES_READ_DB` | Sí | Base de datos | `library` |
| `POSTGRES_WRITE_USER`, `POSTGRES_WRITE_PASS` | Sí | Credenciales de escritura | `library` |
| `POSTGRES_READ_USER`, `POSTGRES_READ_PASS` | Sí | Credenciales de lectura. Con Docker deben coincidir con las del `.env` de la raíz, que crean el superusuario del contenedor `postgres` | `library` |
| `RABBITMQ_HOST` | Sí | Host de RabbitMQ. Con Docker usa `rabbitmq` | `127.0.0.1` |
| `RABBITMQ_LOGIN`, `RABBITMQ_PASSWORD` | Sí | Credenciales de RabbitMQ (con Docker, las mismas que en el `.env` de la raíz) | `guest` |
| `RABBITMQ_EXCHANGE` | Sí | Exchange donde se publican los eventos | `library` |
| `RABBITMQ_MAX_RETRIES` | Sí | Reintentos de un mensaje antes de descartarlo | `3` |
| `REDIS_URL` | Sí | DSN de Redis para sesiones y mensajes ya procesados. Con Docker, `redis://<REDIS_PASSWORD>@redis:6379` | `redis://127.0.0.1:6379` |
| `S3_ENDPOINT` | Sí | URL del servicio S3 de los recursos de las ediciones. Con Docker, `http://seaweedfs:8333` | `http://127.0.0.1:8333` |
| `S3_REGION` | Sí | Región S3 (SeaweedFS la ignora) | `us-east-1` |
| `S3_ACCESS_KEY`, `S3_SECRET_KEY` | Sí | Credenciales S3 (con Docker, las mismas que en el `.env` de la raíz) | `library`, `changeme-s3-secret` |
| `S3_BUCKET` | Sí | Bucket de los recursos (con Docker, el mismo que en el `.env` de la raíz) | `library-resources` |
| `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY`, `JWT_PASSPHRASE` | Sí | Claves y frase de paso para firmar los JWT | `config/jwt/*.pem`, `ChangeMe` |
| `GOOGLE_BOOKS_API_KEY` | No | Clave de la API de Google Books | Vacía |
| `BEHAT_USER`, `BEHAT_PASSWORD` | Para Behat | Email y contraseña de un usuario existente para el paso `an authentificated user` | Vacías |

Las claves JWT no están en el repositorio (`apps/api/config/jwt/*.pem` está ignorado). Genéralas una vez, con los contenedores arrancados:

```bash
make api-console c=lexik:jwt:generate-keypair
```

## Trabajar sin Docker

El repositorio no define un servidor web fuera de Docker: Nginx y PHP-FPM solo se configuran en [compose.api.yml](compose.api.yml). Sin Docker puedes instalar dependencias, preparar la base de datos y ejecutar las pruebas y la consola, igual que hace la CI. Para servir la API, usa Docker ([README de la raíz](../../README.md)).

Necesitas PHP 8.4.27 o superior con las extensiones `amqp`, `ctype`, `curl`, `iconv`, `intl`, `json`, `pdo_pgsql`, `redis` y `zip` (las mismas que instala la CI en [api.yml](../../.github/workflows/api.yml)), Composer 2, y PostgreSQL 14, RabbitMQ y Redis accesibles. Desde `apps/api/`:

```bash
# 1. Dependencias
composer install

# 2. Configuración (ajusta hosts y credenciales a tus servicios)
cp .env.dist .env

# 3. Esquema y datos de países en una base de datos "library" existente
psql -h 127.0.0.1 -U library -d library -v ON_ERROR_STOP=1 -f migrations/library.sql

# 4. Comprobar que el kernel arranca y ver las rutas
bin/console debug:router

# 5. Crear las colas de RabbitMQ de los suscriptores
bin/console library:rabbit:configure
```

## Imagen Docker

`compose.api.yml` construye la imagen PHP desde el [Dockerfile](Dockerfile) con la etapa `library_php_dev` y monta `apps/api` en `/srv/app`, de modo que los cambios en el código no requieren reconstruir.

| Etapa | Uso |
|---|---|
| `library_php_base` | PHP 8.4.27 FPM Alpine con extensiones y configuración de PHP-FPM, sin código ni herramientas de desarrollo |
| `library_php_vendor` | Solo de construcción: `composer install --no-dev`, autoload optimizado y `cache:warmup` de `prod` |
| `library_php_dev` | Desarrollo (la que usa compose). Añade Composer, Symfony CLI, git y un driver de cobertura: `COVERAGE_DRIVER=pcov` (por defecto), `xdebug` o `none` |
| `library_php` | Producción: base más la aplicación construida (con `vendor/` y la caché calentada), `symfony.prod.ini` (OPcache sin comprobar ficheros y preload) y usuario `www-data`. Funciona sin montar nada y nunca incluye PCOV, Xdebug, Composer ni git |

Al arrancar, el entrypoint del contenedor `api` ejecuta `composer install` (fuera de `prod`) y espera a que la base de datos responda ([docker/php/docker-entrypoint.sh](docker/php/docker-entrypoint.sh)).

El contenedor `api` también monta el repositorio entero en `/srv/repo`, para que GrumPHP encuentre el `.git` de la raíz.

### Consumidores de RabbitMQ

El servicio `api_consumers` arranca con [docker/php/library-consumers.sh](docker/php/library-consumers.sh): espera a que `api` haya instalado `vendor/`, ejecuta `library:rabbit:configure`, genera un programa de Supervisor por suscriptor con `library:rabbit:generate-supervisor` (en `/tmp/supervisor.d`, dentro del contenedor) y lanza supervisord ([docker/php/supervisord.conf](docker/php/supervisord.conf)). No repite `composer install` ni las migraciones.

Cada suscriptor nuevo (una clase que implementa `DomainEventSubscriber`) tiene su cola y su proceso sin tocar el compose. El contenedor está sano cuando todos los programas están en `RUNNING`.

| Comando | Para qué |
|---|---|
| `make api-consumers-logs` | Ver los logs de los consumidores |
| `make api-consumers-status` | Ver el estado de cada programa |
| `make api-consumers-restart` | Reiniciar los consumidores. Hace falta después de cambiar un suscriptor o su configuración, porque son procesos PHP largos y no recargan el código |

La imagen de producción se construye, desde la raíz, con:

```bash
docker build --target library_php -t library-php apps/api
```

La imagen no lleva `.env` ni las claves JWT (`config/jwt/*.pem`): la configuración llega por variables de entorno y las claves se montan al arrancar. Por ejemplo:

```bash
docker run --env-file apps/api/.env -v "$PWD/apps/api/config/jwt:/srv/app/config/jwt:ro" library-php
```

## Acceso a la aplicación

Con los contenedores arrancados:

- **API**: `http://localhost/api/v1` o `https://localhost/api/v1`. Nginx publica los puertos 80 y 443 del host; el certificado HTTPS es autofirmado, así que con `curl` necesitas `-k`.
- **Consola de Symfony**: `make api-console c="..."` (por ejemplo `make api-console c=about`) o `make api-sh` para entrar al contenedor.

No hay endpoint de salud ni documentación servida por la aplicación. La API HTTP está especificada con OpenAPI en [docs/openapi/openapi.yml](docs/openapi/openapi.yml) y los eventos de dominio de RabbitMQ con AsyncAPI en [docs/asyncapi/asyncapi.yml](docs/asyncapi/asyncapi.yml). `make api-docs-lint` valida ambas (la CI también).

Para consultar y probar la API en el navegador, `make api-docs` arranca Swagger UI (servicio `api_docs`, perfil `docs`, imagen oficial `swaggerapi/swagger-ui`) y abre https://localhost/docs/. Nginx le pasa `/docs/`, así que «Try it out» llama a la API desde el mismo origen: pide un token con `POST /api/v1/login_check` y pégalo en «Authorize». `make api-docs-down` lo para. Swagger UI lee los ficheros de `docs/openapi/` montados, así que basta con recargar la página tras editarlos.

### Crear un usuario y obtener un token

1. Crea un usuario con id (UUID), email, contraseña y si está activo (`1` o `0`):

   ```bash
   make api-console c="library:user:create 3f1f7c5e-8a8e-4b43-9b0e-0d5f1c3a2b10 tu@email.com tu-contraseña 1"
   ```

2. Pide un token JWT (el campo `username` es el email) y guárdalo en una variable:

   ```bash
   TOKEN=$(curl -sk -X POST https://localhost/api/v1/login_check \
     -H 'Content-Type: application/json' \
     -d '{"username": "tu@email.com", "password": "tu-contraseña"}' \
     | php -r 'echo json_decode(stream_get_contents(STDIN))->token;')
   ```

3. Envía el token en las demás peticiones:

   ```bash
   curl -k 'https://localhost/api/v1/author/match?keywords=borges&limit=10' \
     -H "Authorization: Bearer $TOKEN"
   ```

## Rutas principales

Todas las rutas están en `config/routes/` con el prefijo `/api/v1`. `make api-console c=debug:router` muestra la lista completa.

| Tipo | Método | Ruta | Finalidad | Acceso |
|---|---|---|---|---|
| REST | POST | `/api/v1/login_check` | Obtener un token JWT con email y contraseña | Público (firewall `login`) |
| REST | GET | `/api/v1/author/match` | Buscar autores (`keywords`, `offset`, `limit`) | Firewall `api` (JWT) |
| REST | GET | `/api/v1/author/{id}` | Obtener un autor | Firewall `api` (JWT) |
| REST | POST | `/api/v1/author` | Crear un autor | Firewall `api` (JWT) |
| REST | POST | `/api/v1/book` | Crear un libro | Firewall `api` (JWT) |
| REST | GET | `/api/v1/edition/seek` | Buscar ediciones en Google Books (`title`, `author`, `publisher`, `isbn`) | Firewall `api` (JWT) |
| REST | GET | `/api/v1/edition/{id}` | Obtener una edición | Firewall `api` (JWT) |
| REST | POST | `/api/v1/edition` | Crear una edición | Firewall `api` (JWT) |
| REST | POST | `/api/v1/edition/{id}/resource` | Subir un fichero a una edición (`multipart/form-data`: `id`, `name` opcional, `file`) | Firewall `api` (JWT) |
| REST | GET | `/api/v1/edition/{id}/resource` | Listar los ficheros de una edición | Firewall `api` (JWT) |
| REST | GET | `/api/v1/edition/{id}/resource/{resourceId}` | Descargar un fichero | Firewall `api` (JWT) |
| REST | GET | `/api/v1/publisher/match` | Buscar editoriales | Firewall `api` (JWT) |
| REST | GET | `/api/v1/country/match` | Buscar países | Firewall `api` (JWT) |

Autores, libros, ediciones y editoriales tienen también `PUT` sobre la ruta base (por ejemplo `PUT /api/v1/author`) y `DELETE` sobre la ruta con id (por ejemplo `DELETE /api/v1/author/{id}`). Los ficheros de una edición se borran con `DELETE /api/v1/edition/{id}/resource/{resourceId}`, y todos a la vez al borrar la edición.

Las reglas `access_control` de [config/packages/security.yaml](config/packages/security.yaml) dejan público solo `/api/v1/login_check` y exigen un JWT válido en el resto de rutas; sin él responden 401. El detalle de cada operación (cuerpos, respuestas y errores) está en la [especificación OpenAPI](docs/openapi/openapi.yml).

## Estructura de `apps/api`

```text
apps/api/
├── bin/console                 # Consola de Symfony
├── config/
│   ├── context/                # Servicios propios: conexiones DBAL, bus de eventos, middleware, repositorios
│   ├── packages/               # Configuración de bundles (seguridad, JWT, messenger, doctrine…)
│   └── routes/                 # Rutas; prod/*/routes.yaml define cada recurso
├── docker/                     # Configuración de las imágenes php y nginx
├── docs/
│   ├── asyncapi/               # Especificación AsyncAPI de los eventos de dominio (RabbitMQ)
│   └── openapi/                # Especificación OpenAPI de la API HTTP
├── migrations/library.sql      # Esquema completo y datos de países (make api-dump / make api-restore)
├── public/index.php            # Front controller
├── scripts/                    # Mensajes de GrumPHP y wrapper de phpcs para PhpStorm
├── src/
│   ├── Application/            # Casos de uso: Command, Query y Subscriber (CQRS)
│   ├── Domain/                 # Agregados, value objects, eventos y contratos de repositorio
│   ├── Entrypoint/
│   │   ├── Controller/         # Controladores HTTP por recurso
│   │   └── Console/            # Comandos: usuarios, fixtures, consumo y configuración de colas
│   ├── Infrastructure/         # DBAL, Google Books, RabbitMQ, autenticación, logging, fixtures
│   └── Kernel.php
├── tests/
│   ├── Application/ Domain/ Infrastructure/ Smoke/   # Pruebas unitarias y de arranque
│   ├── Integration/            # Bus de eventos con Postgres y RabbitMQ reales
│   └── Entrypoint/http/        # Escenarios Behat de la API
├── .env.dist                   # Variables de Symfony de referencia
├── compose.api.yml             # Servicios api, api_consumers y api_nginx (incluido desde la raíz)
├── Dockerfile                  # Etapas library_php_base, library_php_dev y library_php
├── Makefile                    # Objetivos de la API (make api-help desde la raíz)
├── behat.yml  phpunit.xml.dist  phpstan.neon  phpcs.xml  grumphp.yml  redocly.yaml
└── composer.json
```

## Comandos útiles

Desde la raíz del repositorio, con los contenedores arrancados. `make api-help` lista todos los objetivos. Dentro de `apps/api/` se pueden usar sin el prefijo `api-` (`make test`).

| Comando | Descripción |
|---|---|
| `make api-sh` | Abre una shell en el contenedor `api` |
| `make api-console c="..."` | Ejecuta un comando de `bin/console` |
| `make api-cc` | Limpia la caché de Symfony |
| `make api-composer c="..."` | Ejecuta Composer en el contenedor |
| `make api-dump` | Vuelca el esquema y los países a `migrations/library.sql` (sin datos de la biblioteca) |
| `make api-restore` | Carga `migrations/library.sql` en la base de datos `library` |

`make api-restore` carga el esquema en la base de datos `library`. El contenedor de PostgreSQL crea por defecto una base de datos con el nombre del usuario `POSTGRES_READ_USER`; si ese usuario no se llama `library`, crea la base de datos antes de restaurar:

```bash
docker compose exec postgres sh -c 'createdb -U "$POSTGRES_USER" library'
```

Comandos de consola propios (`make api-console c="..."`):

| Comando | Descripción |
|---|---|
| `library:user:create id email password isActive` | Crea un usuario |
| `library:fixtures:generate` / `library:fixtures:clean` | Carga o elimina datos de prueba |
| `library:rabbit:configure` | Declara el exchange y las colas de los suscriptores |
| `library:rabbit:consume cola` | Consume los eventos de una cola de RabbitMQ hasta que se detiene el proceso |
| `library:postgres:consume cantidad` | Procesa eventos guardados en el respaldo de PostgreSQL |
| `library:rabbit:generate-supervisor [ruta] [--output-dir=carpeta]` | Genera un `.conf` de Supervisor por suscriptor (por defecto en `/srv/app/build/supervisord`) |

## Pruebas y calidad

| Comando | Qué ejecuta |
|---|---|
| `make api-test` | PHPUnit, suite por defecto (todo `tests/` salvo `tests/Integration`) |
| `make api-test-integration` | Suite `integration`: necesita PostgreSQL con el esquema cargado y RabbitMQ |
| `make api-behat` | Escenarios Behat contra `https://api_nginx`; necesita `BEHAT_USER` y `BEHAT_PASSWORD` de un usuario existente |
| `make api-lint` | `symfony check:security`, PHPStan, parallel-lint y PHP_CodeSniffer |
| `make api-fix` | Corrige el estilo con `phpcbf` |
| `make api-grumphp` | Ejecuta las comprobaciones de GrumPHP (también se instalan como hook de Git con `make api-composer c=install`) |
| `make api-coverage` | Resumen de cobertura en consola |
| `make api-report` | Cobertura HTML en `gen/coverage`, JUnit en `gen/junit.xml` y métricas en `gen/metrics` |

La cobertura usa PCOV, incluido en la imagen `library_php_dev`. Para usar Xdebug, reconstruye con `COVERAGE_DRIVER=xdebug make build` desde la raíz.

La CI de la API ([.github/workflows/api.yml](../../.github/workflows/api.yml)) se ejecuta en `master` y en los pull requests que tocan `apps/api/`, `docker/`, `docker-compose.yml` o el propio workflow. Tiene cinco trabajos: calidad (sintaxis, phpcs, PHPStan, lint de YAML y contenedor, PHPUnit), integración con PostgreSQL 14 y RabbitMQ 3.13, arranque en `prod` sin dependencias de desarrollo, imagen Docker (PHP 8.4.27 o superior, sin driver de cobertura en producción) y auditoría de dependencias. La imagen no se publica en ningún registro.

## Licencia

MIT: ver [LICENSE](../../LICENSE) en la raíz del repositorio. `composer.json` declara `"license": "MIT"`.
