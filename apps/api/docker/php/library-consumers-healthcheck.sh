#!/bin/sh
# Healthy only when supervisord answers and every consumer program is RUNNING.
# supervisorctl status alone is not enough: it exits 0 for BACKOFF.
out=$(supervisorctl -c "${SUPERVISOR_CONF:-/etc/supervisord.conf}" status 2>&1)
echo "$out"
[ -n "$out" ] || exit 1
echo "$out" | awk '{ if ($2 != "RUNNING") bad = 1 } END { exit bad }'
