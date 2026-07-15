#!/bin/bash
set -e

# Ensure a single Apache MPM (php image needs prefork)
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Railway provides PORT; keep Apache on that port
if [ -n "$PORT" ] && [ "$PORT" != "8080" ]; then
  sed -i "s/Listen 8080/Listen ${PORT}/g" /etc/apache2/ports.conf
  sed -i "s/:8080/:${PORT}/g" /etc/apache2/sites-available/000-default.conf
fi

mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Clear and rebuild caches when env is present
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

exec apache2-foreground
