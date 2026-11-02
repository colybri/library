#!/usr/bin/env bash
# One-shot setup of the Elastic stack (service elastic_setup, profile elastic).
# Runs in the Elasticsearch image as root and is idempotent: every
# `make elastic-up` runs it again and it only creates what is missing.
#
#   1. Certificates: a local CA plus one certificate per service, in the
#      elastic_certs volume. Generated only once; delete the volume to renew.
#   2. Waits until Elasticsearch answers over HTTPS.
#   3. Sets the password of the built-in kibana_system user.
#   4. Creates library_apm, the user the APM Server writes with, and
#      library_beats, the one of Filebeat and Metricbeat.
#   5. Retention: data streams keep 7 days of data at most.
set -euo pipefail

: "${ELASTIC_PASSWORD:?Set ELASTIC_PASSWORD in .env}"
: "${KIBANA_SYSTEM_PASSWORD:?Set KIBANA_SYSTEM_PASSWORD in .env}"
: "${APM_ES_PASSWORD:?Set APM_ES_PASSWORD in .env}"
: "${BEATS_ES_PASSWORD:?Set BEATS_ES_PASSWORD in .env}"
if [ "${#KIBANA_ENCRYPTION_KEY}" -lt 32 ]; then
	echo "[elastic_setup] KIBANA_ENCRYPTION_KEY in .env must have at least 32 characters" >&2
	exit 1
fi
RETENTION="${ELASTIC_RETENTION:-7d}"

CERTS=config/certs
ES_URL=https://elasticsearch:9200

log() { echo "[elastic_setup] $*"; }

# JSON string with the value escaped (passwords may hold " or \)
json_string() {
	local value=${1//\\/\\\\}
	value=${value//\"/\\\"}
	printf '"%s"' "$value"
}

# Request to Elasticsearch as the elastic superuser: es METHOD PATH [JSON]
es() {
	local args=(--silent --show-error --fail-with-body
		--cacert "$CERTS/ca/ca.crt"
		-u "elastic:${ELASTIC_PASSWORD}"
		-H 'Content-Type: application/json'
		-X "$1" "${ES_URL}$2")
	if [ $# -ge 3 ]; then
		args+=(-d "$3")
	fi
	curl "${args[@]}"
}

# 1. Certificates ------------------------------------------------------------
if [ ! -f "$CERTS/ca/ca.crt" ]; then
	log "Creating the certificate authority"
	bin/elasticsearch-certutil ca --silent --pem -out "$CERTS/ca.zip"
	unzip -q -o "$CERTS/ca.zip" -d "$CERTS"
	rm "$CERTS/ca.zip"
fi

if [ ! -f "$CERTS/elasticsearch/elasticsearch.crt" ]; then
	log "Creating the service certificates"
	# One certificate per service, valid for its compose name and for
	# localhost, so the host can reach the published ports over HTTPS.
	cat > "$CERTS/instances.yml" <<-'YAML'
		instances:
		  - name: elasticsearch
		    dns: [elasticsearch, localhost]
		    ip: [127.0.0.1]
		  - name: kibana
		    dns: [kibana, localhost]
		    ip: [127.0.0.1]
	YAML
	bin/elasticsearch-certutil cert --silent --pem \
		--in "$CERTS/instances.yml" \
		--ca-cert "$CERTS/ca/ca.crt" --ca-key "$CERTS/ca/ca.key" \
		-out "$CERTS/certs.zip"
	unzip -q -o "$CERTS/certs.zip" -d "$CERTS"
	rm "$CERTS/certs.zip"
fi

# Its own block: stacks created before the APM Server have no certificate for
# it. The name has a hyphen because PHP rejects URLs whose host has "_", so
# the API reaches the server through the network alias apm-server.
if [ ! -f "$CERTS/apm-server/apm-server.crt" ]; then
	log "Creating the APM Server certificate"
	cat > "$CERTS/instances-apm.yml" <<-'YAML'
		instances:
		  - name: apm-server
		    dns: [apm-server, apm_server, localhost]
		    ip: [127.0.0.1]
	YAML
	bin/elasticsearch-certutil cert --silent --pem \
		--in "$CERTS/instances-apm.yml" \
		--ca-cert "$CERTS/ca/ca.crt" --ca-key "$CERTS/ca/ca.key" \
		-out "$CERTS/apm-certs.zip"
	unzip -q -o "$CERTS/apm-certs.zip" -d "$CERTS"
	rm "$CERTS/apm-certs.zip"
fi

# The Elastic images run as uid 1000, gid 0: group root must read the files.
chown -R root:root "$CERTS"
find "$CERTS" -type d -exec chmod 750 {} +
find "$CERTS" -type f -exec chmod 640 {} +
# The CA certificate is public: PHP-FPM workers (www-data) read it to trust
# the APM Server. Its private key stays readable by group root only.
chmod 755 "$CERTS" "$CERTS/ca"
chmod 644 "$CERTS/ca/ca.crt"

# 2. Wait for Elasticsearch --------------------------------------------------
log "Waiting for Elasticsearch"
until curl --silent --cacert "$CERTS/ca/ca.crt" "$ES_URL" | grep -q 'missing authentication credentials'; do
	sleep 5
done

# 3. kibana_system password --------------------------------------------------
log "Setting the kibana_system password"
# Retried while the security index starts. A 401 that never ends means that
# Elasticsearch rejects ELASTIC_PASSWORD: check it in .env.
attempt=0
until es POST /_security/user/kibana_system/_password "{\"password\":$(json_string "$KIBANA_SYSTEM_PASSWORD")}" > /dev/null; do
	attempt=$((attempt + 1))
	if [ "$attempt" -ge 30 ]; then
		log "Could not set the kibana_system password: check ELASTIC_PASSWORD in .env"
		exit 1
	fi
	sleep 5
done

# 4. APM Server user ---------------------------------------------------------
# Only what the APM Server needs to write its data streams.
log "Creating the library_apm user"
es PUT /_security/role/library_apm_writer '{
	"cluster": ["monitor"],
	"indices": [{
		"names": ["traces-apm*", "logs-apm*", "metrics-apm*"],
		"privileges": ["auto_configure", "create_doc"]
	}, {
		"names": [".apm-agent-configuration", ".apm-source-map"],
		"privileges": ["read"],
		"allow_restricted_indices": true
	}]
}' > /dev/null
es PUT /_security/user/library_apm "{\"password\":$(json_string "$APM_ES_PASSWORD"),\"roles\":[\"library_apm_writer\"],\"full_name\":\"APM Server of the library stack\"}" > /dev/null

# Filebeat and Metricbeat: write their data streams, install their templates,
# ingest pipelines (Filebeat modules) and Kibana dashboards, and read the
# Elasticsearch stats (Metricbeat module elasticsearch).
log "Creating the library_beats user"
es PUT /_security/role/library_beats_writer '{
	"cluster": ["monitor", "read_ilm", "read_pipeline", "manage_ingest_pipelines", "manage_index_templates"],
	"indices": [{
		"names": ["filebeat-*", "metricbeat-*"],
		"privileges": ["create_doc", "auto_configure", "view_index_metadata", "manage"]
	}],
	"applications": [{
		"application": "kibana-.kibana",
		"privileges": ["all"],
		"resources": ["*"]
	}]
}' > /dev/null
es PUT /_security/user/library_beats "{\"password\":$(json_string "$BEATS_ES_PASSWORD"),\"roles\":[\"library_beats_writer\"],\"full_name\":\"Filebeat and Metricbeat of the library stack\"}" > /dev/null

# 5. Retention ---------------------------------------------------------------
# Global retention of data stream lifecycles: every data stream managed by a
# lifecycle keeps $RETENTION at most.
log "Setting the data retention to ${RETENTION}"
es PUT /_cluster/settings "{\"persistent\":{\"data_streams.lifecycle.retention.default\":\"${RETENTION}\",\"data_streams.lifecycle.retention.max\":\"${RETENTION}\"}}" > /dev/null

# The built-in logs, metrics and traces templates (APM ones included) use ILM
# when it is configured, and global retention only applies to data streams
# managed by a lifecycle. Their @custom hooks switch them to the lifecycle.
for type in logs metrics traces; do
	es PUT "/_component_template/${type}@custom" '{
		"template": {
			"settings": {"index.lifecycle.prefer_ilm": false},
			"lifecycle": {"enabled": true}
		},
		"_meta": {"managed_by": "library elastic_setup", "description": "Data stream lifecycle, so the global retention applies"}
	}' > /dev/null
done

# Filebeat and Metricbeat use ILM, not the data stream lifecycle: their own
# policies, created here with the same retention (the beats keep them as they are).
for beat in filebeat metricbeat; do
	es PUT "/_ilm/policy/${beat}" "{
		\"policy\": {
			\"phases\": {
				\"hot\": {\"actions\": {\"rollover\": {\"max_age\": \"1d\", \"max_primary_shard_size\": \"10gb\"}}},
				\"delete\": {\"min_age\": \"${RETENTION}\", \"actions\": {\"delete\": {}}}
			},
			\"_meta\": {\"managed_by\": \"library elastic_setup\"}
		}
	}" > /dev/null
done

log "Done"
