#!/usr/bin/env bash
# One-shot setup of Kibana (service kibana_setup, profile elastic). Idempotent:
# every `make elastic-up` runs it again and leaves Kibana as this folder says.
#
#   setup (default)  1. Imports library.ndjson: data views, the 4 dashboards
#                       and their panels (overwriting them).
#                    2. Creates the connector that writes alerts to the
#                       library-alerts index and the rules in rules/*.json
#                       (deleted and created again, so changes apply).
#   export           Writes the dashboards as they are now in Kibana, with
#                    everything they use, to stdout (make elastic-export).
set -euo pipefail

: "${ELASTIC_PASSWORD:?Set ELASTIC_PASSWORD in .env}"
KIBANA_URL=https://kibana:5601
CA=/certs/ca/ca.crt
DIR=/kibana
CONNECTOR_ID=library-alerts-index
DASHBOARDS=(library-api library-domain library-infra library-errors)

log() { echo "[kibana_setup] $*" >&2; }

# Request to Kibana as the elastic superuser: kb METHOD PATH [curl args...]
kb() {
	local method=$1 path=$2
	shift 2
	curl --silent --show-error --cacert "$CA" -u "elastic:${ELASTIC_PASSWORD}" \
		-H 'kbn-xsrf: library' -X "$method" "${KIBANA_URL}${path}" "$@"
}

# Same, but fails unless the answer is 2xx
kb_ok() {
	local method=$1 path=$2 body code
	shift 2
	body=$(mktemp)
	code=$(kb "$method" "$path" --output "$body" --write-out '%{http_code}' "$@")
	if [ "${code:0:1}" != 2 ]; then
		log "${method} ${path} answered ${code}: $(cat "$body")"
		rm -f "$body"
		return 1
	fi
	cat "$body"
	rm -f "$body"
}

if [ "${1:-setup}" = export ]; then
	objects=$(printf '{"type":"dashboard","id":"%s"},' "${DASHBOARDS[@]}")
	kb_ok POST /api/saved_objects/_export -H 'Content-Type: application/json' \
		-d "{\"objects\":[${objects%,}],\"includeReferencesDeep\":true,\"excludeExportDetails\":true}"
	exit 0
fi

# 1. Dashboards -----------------------------------------------------------------
log "Importing data views and dashboards"
result=$(kb_ok POST '/api/saved_objects/_import?overwrite=true' --form "file=@${DIR}/library.ndjson")
if ! grep -q '"success":true' <<< "$result"; then
	log "The import failed: ${result}"
	exit 1
fi

# 2. Alerts ---------------------------------------------------------------------
# Deleting first lets a changed rule type or connector type apply. 404 is fine.
for file in "$DIR"/rules/*.json; do
	kb DELETE "/api/alerting/rule/$(basename "$file" .json)" --output /dev/null
done
kb DELETE "/api/actions/connector/${CONNECTOR_ID}" --output /dev/null

log "Creating the connector ${CONNECTOR_ID} (index library-alerts)"
kb_ok POST "/api/actions/connector/${CONNECTOR_ID}" -H 'Content-Type: application/json' \
	--data-binary "@${DIR}/connector-library-alerts.json" > /dev/null

for file in "$DIR"/rules/*.json; do
	id=$(basename "$file" .json)
	log "Creating the rule ${id}"
	kb_ok POST "/api/alerting/rule/${id}" -H 'Content-Type: application/json' --data-binary "@${file}" > /dev/null
done

log "Done"
