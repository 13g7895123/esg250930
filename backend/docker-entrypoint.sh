#!/bin/bash
set -e

# Fix permissions for writable directory
echo "Setting up file permissions..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOSTNAME};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" &> /dev/null; do
    echo "MySQL is unavailable - sleeping"
    sleep 2
done
echo "MySQL is up!"

# Run database migrations
echo "Running database migrations..."
cd /var/www/html
php spark migrate --all

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g "daemon off;"