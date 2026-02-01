#!/usr/bin/env bash
set -e

# Get PORT from Railway environment or default to 80
PORT=${PORT:-80}

echo "=================================================="
echo "Starting Toko Inventori Server"
echo "=================================================="
echo "Port: $PORT"
echo "Date: $(date)"
echo "Working Dir: $(pwd)"
echo "--------------------------------------------------"

# Ensure required directories exist
mkdir -p /app/logs

# Update Nginx configuration with PORT
echo "Configuring Nginx to listen on port $PORT..."
sed -i "s/listen \$PORT;/listen $PORT;/g" /etc/nginx/nginx.conf

# Start PHP-FPM daemon
echo "Starting PHP-FPM..."
php-fpm -D
FPM_PID=$!
echo "PHP-FPM started (PID: $FPM_PID)"

# Give PHP-FPM a moment to start
sleep 2

# Check if PHP-FPM is running
if ! pgrep -x "php-fpm" > /dev/null; then
    echo "ERROR: PHP-FPM failed to start!"
    exit 1
fi

# Start Nginx in foreground (with daemon mode off so container doesn't exit)
echo "Starting Nginx..."
echo "--------------------------------------------------"
exec nginx -g 'daemon off;'
