#!/bin/bash
set -euo pipefail

echo "Initializing entrypoint script..."

mkdir -p var public/uploads migrations

echo "Setting permissions..."
chown -R www-data:www-data var public/uploads
find var public/uploads -type d -exec chmod 775 {} \;
find var public/uploads -type f -exec chmod 664 {} \;

echo "Waiting for the database to be ready..."
MAX_TRIES=30
TRIES=0
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  sleep 2
  TRIES=$((TRIES+1))
  echo "Connection attempt #$TRIES..."
  if [ "$TRIES" -ge "$MAX_TRIES" ]; then
    echo "Failed to connect to the database after $MAX_TRIES attempts. Aborting."
    exit 1
  fi
done
echo "Database is ready!"

if [ ! -f "vendor/autoload.php" ]; then
  echo "Installing Composer dependencies..."
  composer install --prefer-dist --no-interaction
fi

echo "Clearing and warming up Symfony cache..."
php bin/console cache:clear
php bin/console cache:warmup

if [ -z "$(ls -A migrations/*.php 2>/dev/null)" ]; then
  echo "No migrations found. Generating initial migration..."
  php bin/console doctrine:migrations:diff --no-interaction || true
fi

if grep -q "INSERT INTO doctrine_migration_versions" dump.sql; then
  echo "Dump already includes applied migrations. Skipping doctrine:migrations:migrate."
else
  echo "Running migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction
fi


echo "Launching Messenger consumer..."
php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M &
WORKER_PID=$!

echo "Starting Apache..."
apache2-foreground &
APACHE_PID=$!

wait -n

echo "Stopping background processes..."
kill -TERM "$WORKER_PID" 2>/dev/null || true
kill -TERM "$APACHE_PID" 2>/dev/null || true

echo "Entrypoint finished."
