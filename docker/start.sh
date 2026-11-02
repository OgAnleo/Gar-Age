#!/bin/sh
# Container entry point: bring the database schema up to date, cache the
# configuration and start Apache.
set -e

# Migrations need a direct connection: Neon's pooled one (PgBouncer in
# transaction mode) keeps no session state. Run them before config:cache,
# which would freeze DB_URL.
# A database that is still waking up (Neon scales to zero) must not keep
# Apache from starting, or the host never sees the app come up: bound the
# migrations and carry on if they fail.
if ! DB_URL="${DATABASE_URL_UNPOOLED:-${DB_URL:-}}" timeout 90 php artisan migrate --force; then
    echo "start.sh: migrations failed or timed out; starting Apache anyway" >&2
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
