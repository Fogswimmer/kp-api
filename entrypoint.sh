#!/bin/bash
set -euo pipefail

echo "Initializing entrypoint script..."

mkdir -p var public/uploads migrations

echo "Setting permissions..."
chown -R www-data:www-data var public/uploads
find var public/uploads -type d -exec chmod 775 {} \;
find var public/uploads -type f -exec chmod 664 {} \;


if [ ! -f "vendor/autoload.php" ]; then
  echo "Installing Composer dependencies..."
  composer install --prefer-dist --no-interaction --optimize-autoloader --no-dev
else
  echo "Composer dependencies already installed"
fi

echo "Checking database connection..."
timeout=30
counter=0
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1 || [ $counter -eq $timeout ]; do
  echo "Waiting for database connection... ($counter/$timeout)"
  sleep 1
  ((counter++))
done

if [ $counter -eq $timeout ]; then
  echo "Database connection timeout. Continuing without DB checks..."
else
  echo "Database connection established"
fi


echo "Clearing and warming up Symfony cache..."
php bin/console cache:clear
php bin/console cache:warmup


echo "Checking migrations..."
if [ -z "$(ls -A migrations/*.php 2>/dev/null)" ]; then
  echo "No migrations found. Generating initial migration..."
  php bin/console doctrine:migrations:diff --no-interaction || true
fi

PENDING_MIGRATIONS=$(php bin/console doctrine:migrations:status --show-versions | grep "not migrated" | wc -l || echo "0")
if [ "$PENDING_MIGRATIONS" -gt 0 ]; then
  echo "Running $PENDING_MIGRATIONS pending migrations..."
  php bin/console doctrine:migrations:migrate --no-interaction
else
  echo "All migrations are up to date"
fi


cleanup() {
  echo "Stopping background processes..."
  if [ ! -z "${APACHE_PID:-}" ]; then
    kill -TERM "$APACHE_PID" 2>/dev/null || true
    wait "$APACHE_PID" 2>/dev/null || true
  fi
  echo "Cleanup completed"
  exit 0
}

trap cleanup SIGTERM SIGINT

echo "Starting Apache..."
apache2-foreground &
APACHE_PID=$!

echo "Application started successfully!"
echo "Apache PID: $APACHE_PID"

wait

echo "Entrypoint finished."
