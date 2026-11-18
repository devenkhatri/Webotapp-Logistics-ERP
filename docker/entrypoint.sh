#!/bin/sh
set -e

# Ensure required storage and cache directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Adjust directory permissions when running as root
if [ "$(id -u)" = '0' ]; then
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
fi

# Optional wait loop for MySQL database connection
if [ "$WAIT_FOR_DB" = "true" ] && [ "$DB_CONNECTION" = "mysql" ]; then
    echo "Waiting for database connection at ${DB_HOST}:${DB_PORT:-3306}..."
    max_tries=30
    count=0
    until php -r "
        try {
            new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
            exit(0);
        } catch (\Exception \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        count=$((count + 1))
        if [ $count -ge $max_tries ]; then
            echo "Database connection timeout after $max_tries seconds. Proceeding anyway..."
            break
        fi
        sleep 1
    done
    if [ $count -lt $max_tries ]; then
        echo "Database connection established successfully."
    fi
fi

exec "$@"
