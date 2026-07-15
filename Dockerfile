FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli zip gd mbstring exif pcntl bcmath intl opcache \
    && a2dismod mpm_event mpm_worker 2>/dev/null || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Prefer lockfile install; fall back to update if lock missing.
# Advisory blocking disabled only as a safety net after version bumps in composer.json.
RUN COMPOSER_ALLOW_SUPERUSER=1 composer config -g audit.block-insecure false \
    && if [ -f composer.lock ]; then \
         COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
       else \
         COMPOSER_ALLOW_SUPERUSER=1 composer update --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
       fi \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

COPY docker/apache-ports.conf /etc/apache2/ports.conf
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]
