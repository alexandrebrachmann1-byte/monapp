#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
    sed -i 's|^APP_BASE_PATH=.*|APP_BASE_PATH=|' .env
    sed -i 's|^DB_HOST=.*|DB_HOST=db|' .env
    sed -i 's|^DB_USER=.*|DB_USER=framework|' .env
    sed -i 's|^DB_PASS=.*|DB_PASS=framework|' .env
fi

if [ ! -d vendor ]; then
    composer install --no-interaction
fi

mkdir -p storage/logs
chown -R www-data:www-data storage || true

exec apache2-foreground