#!/bin/bash
set -e
cd /var/www/html

# Render injects PORT
export PORT="${PORT:-10000}"

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
  echo "Generating APP_KEY..."
  export APP_KEY=$(php -r "echo 'base64:'.base64_encode(random_bytes(32));")
fi

# Write runtime env for artisan
php -r "
\$env = [
  'APP_NAME' => getenv('APP_NAME') ?: 'Agri-market',
  'APP_ENV' => getenv('APP_ENV') ?: 'production',
  'APP_KEY' => getenv('APP_KEY') ?: '',
  'APP_DEBUG' => getenv('APP_DEBUG') ?: 'false',
  'APP_URL' => getenv('APP_URL') ?: '',
  'DB_CONNECTION' => getenv('DB_CONNECTION') ?: 'pgsql',
  'DB_HOST' => getenv('DB_HOST') ?: '127.0.0.1',
  'DB_PORT' => getenv('DB_PORT') ?: '5432',
  'DB_DATABASE' => getenv('DB_DATABASE') ?: 'agri',
  'DB_USERNAME' => getenv('DB_USERNAME') ?: 'agri',
  'DB_PASSWORD' => getenv('DB_PASSWORD') ?: '',
  'SESSION_DRIVER' => getenv('SESSION_DRIVER') ?: 'database',
  'CACHE_STORE' => getenv('CACHE_STORE') ?: 'database',
  'QUEUE_CONNECTION' => getenv('QUEUE_CONNECTION') ?: 'database',
  'LOG_CHANNEL' => 'stderr',
  'FILESYSTEM_DISK' => 'public',
];
\$lines = [];
foreach (\$env as \$k => \$v) { \$lines[] = \$k.'='.\$v; }
file_put_contents('.env', implode(\"\\n\", \$lines).\"\\n\");
"

mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/public bootstrap/cache
chmod -R 775 storage bootstrap/cache

php artisan config:clear || true
php artisan migrate --force || true
php artisan storage:link || true

# Prefer SPA index for non-API if present
if [ -f public/spa/index.html ]; then
  # ensure logo reachable
  cp -n public/spa/logo.png public/logo.png 2>/dev/null || true
fi

echo "Starting Agri-market on port $PORT"
# PHP built-in server with router for SPA + API
exec php -S 0.0.0.0:$PORT -t public public/router.php
