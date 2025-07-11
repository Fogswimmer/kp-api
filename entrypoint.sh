#!/bin/bash
set -euo pipefail

echo "Initializing entrypoint script..."

mkdir -p var public/uploads migrations

echo "Setting permissions..."
chown -R www-data:www-data var public/uploads
chmod -R 775 var public/uploads

# echo "Waiting for the database to be ready..."
# MAX_TRIES=30
# TRIES=0
# until php -r "new PDO(getenv('DATABASE_URL'));" > /dev/null 2>&1; do
#   sleep 2
#   TRIES=$((TRIES+1))
#   echo "Connection attempt #$TRIES..."
#   if [ "$TRIES" -ge "$MAX_TRIES" ]; then
#     echo "Failed to connect to the database after $MAX_TRIES attempts. Aborting."
#     exit 1
#   fi
# done
# echo "Database is ready!"

if [ ! -f "vendor/autoload.php" ]; then
  echo "Installing Composer dependencies..."
  composer install --prefer-dist --no-interaction
fi

echo "Clearing and warming up Symfony cache..."
php bin/console cache:clear
php bin/console cache:warmup

if [ -z "$(find migrations -type f -name '*.php' 2>/dev/null)" ]; then
  echo "No migrations found. Generating initial migration..."
  php bin/console doctrine:migrations:diff --no-interaction || true
fi

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction || {
  echo "Failed to apply migrations."
  exit 1
}

# echo "Launching Messenger consumer..."
# php bin/console messenger:consume async --time-limit=3600 --memory-limit=128M &
# WORKER_PID=$!

echo "Starting Apache..."
apache2-foreground &
APACHE_PID=$!

wait -n

# echo "Stopping background processes..."
# kill -TERM "$WORKER_PID" 2>/dev/null || true
# kill -TERM "$APACHE_PID" 2>/dev/null || true

echo "Entrypoint finished."
