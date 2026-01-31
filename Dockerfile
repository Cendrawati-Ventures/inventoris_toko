# syntax=docker/dockerfile:1
FROM dunglas/frankenphp:1.1-php8.2

# Install PHP extensions
RUN install-php-extensions pdo pdo_pgsql

# Set working directory
WORKDIR /app

# Copy application
COPY . /app

# Ensure log directory exists
RUN mkdir -p /app/logs && chmod -R 755 /app/logs

# Copy Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Expose port (Railway uses PORT)
EXPOSE 80
