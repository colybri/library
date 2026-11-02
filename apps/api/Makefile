# API (Symfony) targets. Run them from the repo root with the api- prefix
# (make api-test) or from this folder without it (make test).
# docker compose walks up to the root docker-compose.yml.

DOCKER_COMP = docker compose
EXEC        = $(DOCKER_COMP) exec api

.DEFAULT_GOAL = help
.PHONY        = help sh test test-integration behat grumphp lint fix report coverage dump restore composer console cc cache

## —— The Library API ———————————————————————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

sh: ## Connect to the PHP FPM container
	@$(EXEC) sh

## —— Phpunit ————————————————————————————————————————————————————————————————
test: ## Run the unit tests
	@$(EXEC) ./vendor/bin/phpunit

test-integration: ## Run the tests that need Postgres and RabbitMQ (message bus)
	@$(EXEC) ./vendor/bin/phpunit --testsuite integration

## —— Behat ————————————————————————————————————————————————————————————————
behat: ## Run the acceptance tests
	@$(EXEC) ./vendor/bin/behat --colors

## —— GrumPHP ————————————————————————————————————————————————————————————————
grumphp: ## Run every GrumPHP task
	@$(DOCKER_COMP) exec -w /srv/repo/apps/api api ./vendor/bin/grumphp run

## —— Static analysis —————————————————————————————————————————————————————————
lint: ## Run static quality analysis tools
	@$(EXEC) symfony check:security
	@$(EXEC) ./vendor/bin/phpstan analyse -c phpstan.neon
	@$(EXEC) ./vendor/bin/parallel-lint --exclude .git --exclude app --exclude vendor . --colors
	@$(EXEC) ./vendor/bin/phpcs --standard=./phpcs.xml --error-severity=1 --warning-severity=8

fix: ## Fix errors according to code standard
	@$(EXEC) ./vendor/bin/phpcbf --standard=./phpcs.xml --error-severity=1 --warning-severity=8

report: ## Generate coverage (gen/coverage), JUnit (gen/junit.xml) and metrics (gen/metrics) reports
	@$(EXEC) ./vendor/bin/phpunit --coverage-html ./gen/coverage --log-junit ./gen/junit.xml
	@$(EXEC) ./vendor/bin/phpmetrics --junit=./gen/junit.xml --report-html=./gen/metrics ./src

coverage: ## Print the code coverage summary in the console
	@$(EXEC) ./vendor/bin/phpunit --coverage-text --only-summary-for-coverage-text

## —— Postgres ————————————————————————————————————————————————————————————————
dump: ## Dump the schema and the countries data to migrations/library.sql (no library data)
	@$(DOCKER_COMP) exec -T postgres sh -c 'pg_dump -U "$$POSTGRES_USER" --schema-only --no-owner library; pg_dump -U "$$POSTGRES_USER" --data-only -t public.countries library' > ./migrations/library.sql

restore: ## Restore database from migrations
	@$(DOCKER_COMP) exec -T postgres sh -c 'psql -U "$$POSTGRES_USER" library' < ./migrations/library.sql

## —— Composer ——————————————————————————————————————————————————————————————
composer: ## Execute composer with parameter "c=", example: make api-composer c="require vendor/package"
	@$(EXEC) composer $(c) --no-ansi --no-interaction

## —— Symfony ———————————————————————————————————————————————————————————————
console: ## List all Symfony commands or pass the parameter "c=" to run one, example: make api-console c=about
	@$(EXEC) bin/console $(c)

cc: c=c:c ## Clear the cache
cc: console

cache: ## Clear the cache and dump the autoloader
	@$(EXEC) sh -c 'bin/console cache:clear && composer dump-autoload'
