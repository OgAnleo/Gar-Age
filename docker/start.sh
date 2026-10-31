#!/bin/sh
# Container entry point: bring the database schema up to date, cache the
# configuration and start Apache.
set -e

# Migrations need a direct connection: Neon's pooled one (PgBouncer in
# transaction mode) keeps no session state. Run them before config:cache,
# which would freeze DB_URL.
DB_URL="${DATABASE_URL_UNPOOLED:-${DB_URL:-}}" php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
