#!/usr/bin/env sh
set -eu

echo "Waiting for MariaDB..."
until php -r '
try {
    new PDO(
        "mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"),
        getenv("DB_USERNAME"),
        getenv("DB_PASSWORD")
    );
} catch (Throwable $e) {
    exit(1);
}
'; do
    sleep 2
done

php artisan config:clear
php artisan migrate --force

if [ "${RUN_SEEDER:-false}" = "true" ]; then
    php artisan db:seed --force
fi

php artisan schedule:work >/proc/1/fd/1 2>/proc/1/fd/2 &

exec "$@"
