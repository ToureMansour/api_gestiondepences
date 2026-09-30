#!/bin/sh
set -e

cd /app

php artisan migrate --force --no-interaction
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=EmployeeUserSeeder --force
php artisan db:seed --class=SettingsSeeder --force

php artisan storage:link || true
php artisan config:cache
php artisan route:cache

exec frankenphp run --config /app/Caddyfile