#!/bin/sh
# Command of the api_consumers container: declares the RabbitMQ exchanges and
# queues, writes one supervisor program per subscriber and runs supervisord.
# It is not "php" nor "bin/console", so docker-entrypoint does not run
# composer install or the migrations again: the api container does that.
set -e

PROGRAMS_DIR=/tmp/supervisor.d

cd "$APP_HOME"

# The code is mounted from the host and api installs the dependencies.
ATTEMPTS_LEFT=120
until [ -f vendor/autoload_runtime.php ]; do
	ATTEMPTS_LEFT=$((ATTEMPTS_LEFT - 1))
	if [ $ATTEMPTS_LEFT -eq 0 ]; then
		echo "vendor/ is still missing: is the api container running composer install?" >&2
		exit 1
	fi
	echo "Waiting for composer install in the api container..."
	sleep 2
done

mkdir -p var/cache var/log

bin/console library:rabbit:configure

rm -rf "$PROGRAMS_DIR"
bin/console library:rabbit:generate-supervisor "$APP_HOME" --output-dir="$PROGRAMS_DIR" -v

exec supervisord -c /etc/supervisord.conf
