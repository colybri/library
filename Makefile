# Root Makefile: Docker lifecycle of the whole monorepo.
# Each app has its own Makefile; call its targets with the app prefix:
#   make api-test    make api-composer c="require vendor/package"    make api-help

DOCKER_COMP = USER_ID=$$(id -u) GROUP_ID=$$(id -g) docker compose
CONT_IP     = docker inspect --format="{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}"

.DEFAULT_GOAL = help
.PHONY        = help build up start stop down logs ps pgadmin rabbit

## —— The Library monorepo ———————————————————————————————————————————————————
help: ## Outputs this help screen (app targets: make api-help)
	@grep -E '(^[a-zA-Z0-9_%-]+:.*?##.*$$)|(^##)' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start every app and the infrastructure in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

stop: ## Stop the containers
	@$(DOCKER_COMP) stop

down: ## Remove the containers
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

ps: ## List the containers
	@$(DOCKER_COMP) ps

## —— Infrastructure ——————————————————————————————————————————————————————————
pgadmin: ## Open pgadmin on browser
	@xdg-open http://$$($(CONT_IP) $$($(DOCKER_COMP) ps -q pgadmin))

rabbit: ## Open the RabbitMQ management UI on browser
	@xdg-open http://$$($(CONT_IP) $$($(DOCKER_COMP) ps -q rabbitmq)):15672

## —— Apps ————————————————————————————————————————————————————————————————————
api-%: ## Run a target of apps/api/Makefile, example: make api-test
	@$(MAKE) --no-print-directory -C apps/api $*
