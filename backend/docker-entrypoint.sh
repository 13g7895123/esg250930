#!/bin/bash
set -e

# Fix permissions for writable directory
echo "Setting up file permissions..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable

# Update .env file with environment variables
echo "Configuring environment..."
cd /var/www/html
if [ ! -f .env ]; then
    cp env .env
fi

# Set CI environment
sed -i "s/# CI_ENVIRONMENT = production/CI_ENVIRONMENT = ${CI_ENVIRONMENT:-production}/" .env

# Set database configuration
sed -i "s/# database.default.hostname = localhost/database.default.hostname = ${DB_HOSTNAME}/" .env
sed -i "s/# database.default.database = ci4/database.default.database = ${DB_DATABASE}/" .env
sed -i "s/# database.default.username = root/database.default.username = ${DB_USERNAME}/" .env
sed -i "s/# database.default.password = root/database.default.password = ${DB_PASSWORD}/" .env
sed -i "s/# database.default.DBDriver = MySQLi/database.default.DBDriver = MySQLi/" .env

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