# Executables (local)
DOCKER_COMP = docker-compose

# Docker containers
PHP_CONT = @docker exec -it library
DATABASE_CONT = @docker exec -it library_postgres

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
SYMFONY  = $(PHP_CONT) bin/console
POSTGRES  = $(PHP_CONT) bin/console

WORKDIR  = /srv/app

# Misc
.DEFAULT_GOAL = help
.PHONY        = help build up start down logs sh composer vendor sf cc

# Include env variables
include .env
export $(shell sed 's/=.*//' .env)


## ——  The Symfony-docker Makefile  ——————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Phpunit ————————————————————————————————————————————————————————————————
test: ## Builds the Docker images
	@$(DOCKER_COMP) exec library ./vendor/bin/phpunit --coverage-text ./tests

## —— Docker ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

stop: ## Stop the docker hub
	@$(DOCKER_COMP) stop

down: ## Remove the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

## —— Postgres ————————————————————————————————————————————————————————————————

dump: ## Dump sql database file on migrations folder
	$(DATABASE_CONT) /bin/bash -c "PGPASSWORD=${POSTGRES_PASS} pg_dump --username ${POSTGRES_USER} library" > ./migrations/library.sql

restore: ## Restore database from migrations
	@$(DATABASE_CONT) /bin/bash -c "PGPASSWORD=${POSTGRES_PASS} psql --username ${POSTGRES_USER} library" < ./migrations/library.sql


## —— Composer ——————————————————————————————————————————————————————————————
# 🐘 Composer
composer: ## Execute composer with parameter "c=" to run a given command, example: make composer c="require vendor/package"
	@docker exec -it library  \
		composer $(c)\
			--ignore-platform-reqs \
			--no-ansi \
			--no-interaction

## —— Symfony ———————————————————————————————————————————————————————————————
sf: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: sf