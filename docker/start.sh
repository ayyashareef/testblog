#!/bin/sh
set -e
cd /app

# Render's generated secrets are plain base64; Laravel expects the prefix.
if [ -z "$APP_KEY" ]; then
    APP_KEY="$(php -r 'echo base64_encode(random_bytes(32));')"
fi
case "$APP_KEY" in
    base64:*) ;;
    *) APP_KEY="base64:$APP_KEY" ;;
esac
export APP_KEY
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:$PORT}}"

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
touch database/database.sqlite

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Free hosting has no cron, so the scheduler runs inside the container, and
# the feeds are read once straight away so the first visitor sees news.
(php artisan news:fetch && php artisan news:snapshot) &
php artisan schedule:work > /dev/null &

exec php artisan serve --host=0.0.0.0 --port="$PORT"
