# Executables (local)
DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = @docker exec -it library
DATABASE_CONT = @docker exec -it library_postgres
PG_ADMIN_CONT = @docker exec -it library_pgadmin
PG_ADMIN_CONT_IP = $$(docker inspect --format="{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}" library_pgadmin)

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console
POSTGRES  = $(PHP_CONT) bin/console

WORKDIR  = /srv/app


# Include env variables
include .env
export $(shell sed 's/=.*//' .env)

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start stop down logs sh test lint report dump restore composer vendor sf cc


## ——  The Library Makefile  ———————————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'


## —— Docker ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@USER_ID=$(id -g) GROUP_ID=$(id -g) $(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@USER_ID=$(id -g) GROUP_ID=$(id -g) $(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

stop: ## Stop the docker hub
	@USER_ID=$(id -g) GROUP_ID=$(id -g) $(DOCKER_COMP) stop

down: ## Remove the docker hub
	@USER_ID=$(id -g) GROUP_ID=$(id -g) $(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@USER_ID=$(id -g) GROUP_ID=$(id -g) $(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

## —— Phpunit ————————————————————————————————————————————————————————————————
test: ## Builds the Docker images
	@$(DOCKER_COMP) exec library ./vendor/bin/phpunit

## —— Behat ————————————————————————————————————————————————————————————————
behat: ## Fix errors according to code standard
	@$(DOCKER_COMP) exec library ./vendor/bin/behat --colors

## —— GrumPHP ————————————————————————————————————————————————————————————————
grumphp: ## Fix errors according to code standard
	@$(DOCKER_COMP) exec library ./vendor/bin/grumphp run

## —— Static analysis —————————————————————————————————————————————————————————
lint: ## Run stactic quality analisys tools
	@$(DOCKER_COMP) exec library symfony check:security
	@$(DOCKER_COMP) exec library ./vendor/bin/phpstan analyse -c phpstan.neon
	@$(DOCKER_COMP) exec library ./vendor/bin/parallel-lint --exclude .git --exclude app --exclude vendor . --colors
	@$(DOCKER_COMP) exec library ./vendor/bin/phpcs --standard=./phpcs.xml --error-severity=1 --warning-severity=8

fix: ## Fix errors according to code standard
	@$(DOCKER_COMP) exec library ./vendor/bin/phpcbf --standard=./phpcs.xml --error-severity=1 --warning-severity=8



report: ## Generate static code reports
	@$(DOCKER_COMP) exec library ./vendor/bin/phpunit --coverage-html ./gen/coverage ./tests
	@$(DOCKER_COMP) exec library ./vendor/bin/phpmetrics --junit=./gen/coverage/index.xml --report-html=./gen/metrics/index.html ./src

## —— Postgres ————————————————————————————————————————————————————————————————

pgadmin: ## Dump sql database file on migrations folder
	@xdg-open http://$(PG_ADMIN_CONT_IP)

dump: ## Dump sql database file on migrations folder
	$(DATABASE_CONT) /bin/bash -c "PGPASSWORD=${POSTGRES_PASS} pg_dump --username ${POSTGRES_USER} library" > ./migrations/library.sql

restore: ## Restore database from migrations
	@$(DATABASE_CONT) /bin/bash -c "PGPASSWORD=${POSTGRES_PASS} psql --username ${POSTGRES_USER} library" < ./migrations/library.sql


## —— Composer ——————————————————————————————————————————————————————————————
# 🐘 Composer
composer: ## Execute composer with parameter "c=" to run a given command, example: make composer c="require vendor/package"
	@docker exec -it library  \
		composer $(c)\
			--no-ansi \
			--no-interaction

## —— Symfony ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf