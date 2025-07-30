#!/bin/bash
set -euo pipefail

echo "Initializing entrypoint script..."

echo "Creating directories..."
mkdir -p var/cache var/log var/sessions public/uploads migrations

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

if [ "${SKIP_DB_CHECK:-false}" != "true" ] && [ "${CONTAINER_TYPE:-app}" != "worker" ]; then
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
else
  echo "Skipping database check (worker mode or explicitly disabled)"
fi

if [ "${SKIP_CACHE_WARMUP:-false}" != "true" ] && [ "${CONTAINER_TYPE:-app}" != "worker" ]; then
    echo "Clearing and warming up Symfony cache..."
    php bin/console cache:clear --env=prod --no-debug
    php bin/console cache:warmup --env=prod --no-debug
else
    echo "⏭Skipping cache operations (worker mode or explicitly disabled)"
fi

echo "Checking migrations..."
if [ -z "$(ls -A migrations/*.php 2>/dev/null)" ]; then
    echo "No migrations found. Generating initial migration..."
    php bin/console doctrine:migrations:diff --no-interaction || true
fi

if php bin/console doctrine:migrations:status 2>/dev/null | grep -q "not migrated"; then
    echo "Running pending migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction
else
    echo "All migrations are up to date"
fi

cleanup() {
    echo "Stopping background processes..."
    if [ ! -z "${WORKER_PID:-}" ]; then
        kill -TERM "$WORKER_PID" 2>/dev/null || true
        wait "$WORKER_PID" 2>/dev/null || true
    fi
    if [ ! -z "${APACHE_PID:-}" ]; then
        kill -TERM "$APACHE_PID" 2>/dev/null || true
        wait "$APACHE_PID" 2>/dev/null || true
    fi
    echo "Cleanup completed"
    exit 0
}

trap cleanup SIGTERM SIGINT

if [ "${START_WORKER:-false}" = "true" ]; then
  echo "Launching Messenger consumer..."
  php bin/console messenger:consume async -vvv \
    --time-limit=3600 \
    --memory-limit=128M \
    --failure-limit=3 \
    --quiet &
  WORKER_PID=$!
  echo "Worker started with PID $WORKER_PID"
else
  echo "Skipping worker start (handled by separate container)"
fi

if [ "${CONTAINER_TYPE:-app}" != "worker" ]; then
  echo "Starting Apache..."
  apache2-foreground &
  APACHE_PID=$!
  echo "Apache PID: $APACHE_PID"
else
  echo "Starting Messenger Worker..."
  timeout=60
  counter=0
  until php bin/console messenger:setup-transports 2>/dev/null || [ $counter -eq $timeout ]; do
    echo "Waiting for RabbitMQ... ($counter/$timeout)"
    sleep 2
    ((counter++))
  done

  if [ $counter -eq $timeout ]; then
    echo "RabbitMQ connection timeout"
    exit 1
  fi

  echo "RabbitMQ connection established"
  echo "Starting Messenger consumer..."

  exec php bin/console messenger:consume async -vvv \
    --time-limit=3600 \
    --memory-limit=128M \
    --failure-limit=3 \
    --env=prod
fi
if [ ! -z "${WORKER_PID:-}" ]; then
  echo "Worker PID: $WORKER_PID"
fi

wait

echo "Entrypoint finished."
