#!/bin/bash
set -e

# Fix permissions for writable directory
echo "Setting up file permissions..."
chown -R www-data:www-data /var/www/html/writable
chmod -R 775 /var/www/html/writable

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx..."
nginx -g "daemon off;"