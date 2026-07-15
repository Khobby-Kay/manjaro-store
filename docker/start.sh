#!/bin/bash
set -e

PORT="${PORT:-8080}"

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Built-in server avoids Apache MPM conflicts on Railway's dynamic PORT
exec php -S "0.0.0.0:${PORT}" -t public public/index.php
