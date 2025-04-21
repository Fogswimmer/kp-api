#!/bin/bash
set -e

echo "Initializing..."

echo "Setting permissions..."
chown -R www-data:www-data var public/uploads

if [ ! -d "vendor" ]; then
  echo "Installing dependencies..."
  composer install --no-interaction --prefer-dist --no-scripts
else
  echo "Dependencies already installed"
fi

echo "Waiting for database to be ready..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  sleep 1
done

echo "DB is up - executing command"
php bin/console doctrine:migrations:diff --no-interaction || true
php bin/console doctrine:migrations:migrate --no-interaction

echo "Launching Apache..."
exec apache2-foreground

