# API (Symfony) targets. Run them from the repo root with the api- prefix
# (make api-test) or from this folder without it (make test).
# docker compose walks up to the root docker-compose.yml.

DOCKER_COMP = docker compose
EXEC        = $(DOCKER_COMP) exec api

DOCS_URL = https://localhost/docs/

# Same versions as the docs job of .github/workflows/api.yml
REDOCLY_VERSION  = 1.34.5
ASYNCAPI_VERSION = 2.16.0

.DEFAULT_GOAL = help
.PHONY: help sh consumers-logs consumers-restart consumers-status test test-integration behat grumphp lint docs docs-down docs-lint fix report coverage dump restore composer console cc cache

## —— The Library API ———————————————————————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

sh: ## Connect to the PHP FPM container
	@$(EXEC) sh

## —— RabbitMQ consumers (api_consumers) ————————————————————————————————————
consumers-logs: ## Follow the logs of the RabbitMQ consumers
	@$(DOCKER_COMP) logs --tail=50 --follow api_consumers

consumers-restart: ## Restart the consumers (needed after changing a subscriber)
	@$(DOCKER_COMP) restart api_consumers

consumers-status: ## Show the state of every consumer program
	@$(DOCKER_COMP) exec api_consumers supervisorctl -c /etc/supervisord.conf status

## —— Phpunit ————————————————————————————————————————————————————————————————
test: ## Run the unit tests
	@$(EXEC) ./vendor/bin/phpunit

test-integration: ## Run the tests that need Postgres, RabbitMQ and Redis (message bus)
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

docs: ## Start Swagger UI of docs/openapi (profile docs) and open it in the browser
	@$(DOCKER_COMP) --profile docs up -d api_docs
	@echo "Swagger UI: $(DOCS_URL) (get a token with POST /api/v1/login_check and use Authorize)"
	@(xdg-open $(DOCS_URL) || open $(DOCS_URL)) >/dev/null 2>&1 || true

docs-down: ## Stop Swagger UI
	@$(DOCKER_COMP) --profile docs stop api_docs

docs-lint: ## Validate docs/openapi and docs/asyncapi (runs Node in Docker)
	@docker run --rm -v $(CURDIR):/app -w /app node:22-alpine sh -c '\
		npx --yes @redocly/cli@$(REDOCLY_VERSION) lint && \
		npx --yes @asyncapi/cli@$(ASYNCAPI_VERSION) validate docs/asyncapi/asyncapi.yml'

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
