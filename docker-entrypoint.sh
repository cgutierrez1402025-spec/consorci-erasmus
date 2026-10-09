#!/bin/sh
set -e
cd /var/www
php artisan package:discover --ansi
php artisan migrate --force
if [ "${SEED_DEMO:-false}" = "true" ]; then php artisan db:seed --force || true; fi
php artisan storage:link 2>/dev/null || true
php artisan config:cache && php artisan route:cache && php artisan view:cache
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
