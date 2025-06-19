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

echo "Running migrations..."
if [ -z "$(ls -A migrations 2>/dev/null)" ]; then
    echo "Migration folder is empty — generating initial migration..."
    php bin/console doctrine:migrations:diff --no-interaction
else
    echo "Migration folder not empty — skipping diff"
fi
php bin/console doctrine:migrations:migrate --no-interaction

echo "Running Apache and Worker..."

php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M &
WORKER_PID=$!

apache2-foreground & wait -n

kill -TERM "$WORKER_PID" 2>/dev/null


