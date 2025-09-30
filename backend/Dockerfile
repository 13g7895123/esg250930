FROM php:7.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    nginx \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/writable \
    && chown -R www-data:www-data /var/www/html/writable

# Nginx configuration
RUN echo "server {\n\
    listen 80;\n\
    index index.php index.html;\n\
    error_log  /var/log/nginx/error.log;\n\
    access_log /var/log/nginx/access.log;\n\
    root /var/www/html/public;\n\
    location ~ \.php$ {\n\
        try_files \$uri =404;\n\
        fastcgi_split_path_info ^(.+\.php)(/.+)$;\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        include fastcgi_params;\n\
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;\n\
        fastcgi_param PATH_INFO \$fastcgi_path_info;\n\
    }\n\
    location / {\n\
        try_files \$uri \$uri/ /index.php?\$query_string;\n\
        gzip_static on;\n\
    }\n\
}" > /etc/nginx/sites-available/default

# Copy and set up entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]