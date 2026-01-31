#!/usr/bin/env bash
set -e

PORT=${PORT:-80}

echo "Starting Nginx + PHP-FPM on PORT=${PORT}"

# Update Nginx listen port
sed -i "s/\$PORT/${PORT}/g" /etc/nginx/nginx.conf

# Start PHP-FPM
php-fpm -D

# Start Nginx
exec nginx -g 'daemon off;'
