# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = @docker exec -it library
DATABASE_CONT = @docker exec -it library_postgres
PG_ADMIN_CONT_IP = $$(docker inspect --format="{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}" library_pgadmin)
RABBIT_CONT_IP = $$(docker inspect --format="{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}" library_rabbit)

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
.PHONY        = help build up start stop down logs sh test lint report coverage dump restore composer vendor sf cc


## ——  The Library Makefile  ———————————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'


## —— Docker ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@USER_ID=$$(id -u) GROUP_ID=$$(id -g) $(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@USER_ID=$$(id -u) GROUP_ID=$$(id -g) $(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

stop: ## Stop the docker hub
	@USER_ID=$$(id -u) GROUP_ID=$$(id -g) $(DOCKER_COMP) stop

down: ## Remove the docker hub
	@USER_ID=$$(id -u) GROUP_ID=$$(id -g) $(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@USER_ID=$$(id -u) GROUP_ID=$$(id -g) $(DOCKER_COMP) logs --tail=0 --follow

sh: ## Connect to the PHP FPM container
	@$(PHP_CONT) sh

## —— Phpunit ————————————————————————————————————————————————————————————————
test: ## Builds the Docker images
	@$(DOCKER_COMP) exec library ./vendor/bin/phpunit

test-integration: ## Run the tests that need Postgres and RabbitMQ (message bus)
	@$(DOCKER_COMP) exec library ./vendor/bin/phpunit --testsuite integration

## —— Behat ————————————————————————————————————————————————————————————————
behat: ## Fix errors according to code standard
	@$(DOCKER_COMP) exec library ./vendor/bin/behat --colors

## —— GrumPHP ————————————————————————————————————————————————————————————————
grumphp: ## Fix errors according to code standard
	@$(DOCKER_COMP) exec -w /srv/repo/apps/api library ./vendor/bin/grumphp run

## —— Static analysis —————————————————————————————————————————————————————————
lint: ## Run stactic quality analisys tools
	@$(DOCKER_COMP) exec library symfony check:security
	@$(DOCKER_COMP) exec library ./vendor/bin/phpstan analyse -c phpstan.neon
	@$(DOCKER_COMP) exec library ./vendor/bin/parallel-lint --exclude .git --exclude app --exclude vendor . --colors
	@$(DOCKER_COMP) exec library ./vendor/bin/phpcs --standard=./phpcs.xml --error-severity=1 --warning-severity=8

fix: ## Fix errors according to code standard
	@$(DOCKER_COMP) exec library ./vendor/bin/phpcbf --standard=./phpcs.xml --error-severity=1 --warning-severity=8



report: ## Generate coverage (gen/coverage), JUnit (gen/junit.xml) and metrics (gen/metrics) reports
	@$(DOCKER_COMP) exec library ./vendor/bin/phpunit --coverage-html ./gen/coverage --log-junit ./gen/junit.xml
	@$(DOCKER_COMP) exec library ./vendor/bin/phpmetrics --junit=./gen/junit.xml --report-html=./gen/metrics ./src

coverage: ## Print the code coverage summary in the console
	@$(DOCKER_COMP) exec library ./vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text

## —— Postgres ————————————————————————————————————————————————————————————————

pgadmin: ## Open pgadmin on browser
	@xdg-open http://$(PG_ADMIN_CONT_IP)

dump: ## Dump the schema and the countries data to migrations/library.sql (no library data)
	@docker exec library_postgres sh -c 'pg_dump -U "$$POSTGRES_USER" --schema-only --no-owner library; pg_dump -U "$$POSTGRES_USER" --data-only -t public.countries library' > ./apps/api/migrations/library.sql

restore: ## Restore database from migrations
	@docker exec -i library_postgres sh -c 'psql -U "$$POSTGRES_USER" library' < ./apps/api/migrations/library.sql


## —— Composer ——————————————————————————————————————————————————————————————
# 🐘 Composer
composer: ## Execute composer with parameter "c=" to run a given command, example: make composer c="require vendor/package"
	@docker exec -it library  \
		composer $(c)\
			--no-ansi \
			--no-interaction

## —— Symfony ———————————————————————————————————————————————————————————————
console: ## List all Symfony commands or pass the parameter "c=" to run a given command, example: make sf c=about
	@$(eval c ?=)
	@$(SYMFONY) $(c)

cc: c=c:c ## Clear the cache
cc: console

cache: ## See list of console commands avialable
	@docker exec -it library php bin/console cache:clear && composer dump-autoload

## —— Rabbit ————————————————————————————————————————————————————————————————
rabbit: ## Open message broker on browser
	@xdg-open http://$(RABBIT_CONT_IP):15672
