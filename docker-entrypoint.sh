#!/bin/sh
set -e
# Render inyecta $PORT; Apache debe escuchar en él
PORT="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
php artisan package:discover --ansi || true
php artisan config:cache
php artisan route:cache
php artisan migrate --force
exec "$@"
