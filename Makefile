# Root Makefile: Docker lifecycle of the whole monorepo.
# Each app has its own Makefile; call its targets with the app prefix:
#   make api-test    make api-composer c="require vendor/package"    make api-help

DOCKER_COMP = USER_ID=$$(id -u) GROUP_ID=$$(id -g) docker compose
CONT_IP     = docker inspect --format="{{range .NetworkSettings.Networks}}{{.IPAddress}}{{end}}"

.DEFAULT_GOAL = help
# Every profile, so stop/down/ps also reach the Elastic stack when it runs
DOCKER_ALL  = $(DOCKER_COMP) --profile elastic
ELASTIC_SERVICES = elastic_setup elasticsearch kibana kibana_setup apm_server filebeat metricbeat

.PHONY        = help build up start stop down logs ps pgadmin rabbit elastic-up elastic-down elastic-reset elastic-ca elastic-logs elastic-export kibana dirs

## —— The Library monorepo ———————————————————————————————————————————————————
help: ## Outputs this help screen (app targets: make api-help)
	@grep -E '(^[a-zA-Z0-9_%-]+:.*?##.*$$)|(^##)' $(firstword $(MAKEFILE_LIST)) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: dirs ## Start every app and the infrastructure in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

stop: ## Stop the containers
	@$(DOCKER_ALL) stop

down: ## Remove the containers
	@$(DOCKER_ALL) down --remove-orphans

logs: ## Show live logs
	@$(DOCKER_COMP) logs --tail=0 --follow

ps: ## List the containers
	@$(DOCKER_ALL) ps

## —— Infrastructure ——————————————————————————————————————————————————————————
pgadmin: ## Open pgadmin on browser
	@xdg-open http://$$($(CONT_IP) $$($(DOCKER_COMP) ps -q pgadmin))

rabbit: ## Open the RabbitMQ management UI on browser
	@xdg-open http://$$($(CONT_IP) $$($(DOCKER_COMP) ps -q rabbitmq)):15672

## —— Elastic stack (profile elastic) ———————————————————————————————————————
elastic-up: dirs ## Start everything plus Elasticsearch, Kibana (https://localhost:5601) and the APM Server
	@$(DOCKER_ALL) up --detach

# Bind-mounted folders: if Docker creates them they belong to root, and then
# neither the host user nor `make elastic-ca` can write in var/
dirs:
	@mkdir -p var/log var/xdebug

elastic-down: ## Stop and remove only the Elastic stack, keeping its data
	@$(DOCKER_ALL) rm --stop --force $(ELASTIC_SERVICES)

elastic-reset: ## Delete every Elastic document (volume elastic_data); certificates are kept
	@printf "This deletes every document stored in Elasticsearch. Continue? [y/N] "; read answer; [ "$$answer" = "y" ]
	@$(DOCKER_ALL) rm --stop --force $(ELASTIC_SERVICES)
	@docker volume rm --force library_elastic_data library_kibana_data library_filebeat_data library_metricbeat_data

elastic-ca: ## Copy the local CA to var/elastic-ca.crt, to trust it in your browser
	@mkdir -p var
	@$(DOCKER_ALL) cp elasticsearch:/usr/share/elasticsearch/config/certs/ca/ca.crt var/elastic-ca.crt
	@echo "CA written to var/elastic-ca.crt"

elastic-export: ## Save the library dashboards as they are in Kibana to docker/elastic/kibana/library.ndjson
	@$(DOCKER_ALL) run --rm --no-deps -T kibana_setup bash /kibana/kibana-setup.sh export > docker/elastic/kibana/library.ndjson.tmp \
		&& mv docker/elastic/kibana/library.ndjson.tmp docker/elastic/kibana/library.ndjson \
		|| { rm -f docker/elastic/kibana/library.ndjson.tmp; exit 1; }
	@echo "Dashboards exported: review the diff and commit docker/elastic/kibana/library.ndjson"

elastic-logs: ## Show live logs of the Elastic stack
	@$(DOCKER_ALL) logs --tail=50 --follow $(ELASTIC_SERVICES)

kibana: ## Open Kibana on browser (user elastic, password ELASTIC_PASSWORD from .env)
	@xdg-open https://localhost:5601

## —— Apps ————————————————————————————————————————————————————————————————————
api-%: ## Run a target of apps/api/Makefile, example: make api-test
	@$(MAKE) --no-print-directory -C apps/api $*
