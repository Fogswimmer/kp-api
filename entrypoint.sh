#!/bin/bash
set -euo pipefail

echo "Initializing entrypoint script..."

if [ ! -f ".env" ]; then
  echo "No .env file found. Aborting."
  exit 1
fi

echo "Setting permissions for var и public/uploads..."

if [ "$(id -u)" = "0" ]; then
  echo "Running as root. Setting ownership and permissions..."
  chown -R www-data:www-data var public/uploads
  chmod -R 775 var public/uploads
else
  echo "Not running as root. Skipping chown/chmod."
fi


echo "Wating for the database to be ready..."
MAX_TRIES=30
TRIES=0
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  sleep 2
  TRIES=$((TRIES+1))
  echo "Connection attempt #$TRIES..."
  if [ "$TRIES" -ge "$MAX_TRIES" ]; then
    echo "Failed to connect to the database after $MAX_TRIES attemtps. Aborting."
    exit 1
  fi
done
echo "Database is ready!"

echo "Handling migrations..."
MIGRATION_FILES=$(find migrations -type f -name '*.php' 2>/dev/null || true)
if [ -z "$MIGRATION_FILES" ]; then
  echo "No migrations found. Creating a new one..."
  php bin/console doctrine:migrations:diff --no-interaction
fi

echo "Applying migrations ..."
php bin/console doctrine:migrations:migrate --no-interaction || {
  echo "Failed to apply migrations. Aborting."
  exit 1
}

echo "Launching Messenger consumer and Apache..."

php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M &
WORKER_PID=$!

apache2-foreground & 
APACHE_PID=$!

wait -n

echo "Finishing background processes..."
kill -TERM "$WORKER_PID" 2>/dev/null || true
kill -TERM "$APACHE_PID" 2>/dev/null || true

echo "Finished"
