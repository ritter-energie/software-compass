#!/bin/sh

set -eu

cd /var/www/html

mkdir -p /var/www/html/.tempest/cache
chown -R www-data:www-data /var/www/html/.tempest

run_as_app_user() {
	su -s /bin/sh www-data -c "$1"
}

if [ "${AUTO_DISCOVERY_GENERATE:-true}" = "true" ]; then
	echo "Generating Tempest discovery cache..."
	run_as_app_user 'php ./vendor/bin/tempest discovery:generate --no-interaction'
fi

if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
	echo "Running database migrations..."

	if ! run_as_app_user 'php tempest migrate:up --force'; then
		cat >&2 <<'MESSAGE'

Database migrations failed.

For a disposable local development database, the fastest recovery is:

  docker compose exec app php tempest migrate:fresh --force
  docker compose exec app php tempest database:seed --all --force

If you need to keep existing data, do not run migrate:fresh. Inspect the
migration error above first; Tempest intentionally stops when an already
executed migration file no longer matches the hash stored in the database.

You can temporarily skip automatic migrations with AUTO_MIGRATE=false.
MESSAGE
		exit 1
	fi
fi

exec "$@"

