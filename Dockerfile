# syntax=docker/dockerfile:1
FROM php:8.2-fpm

# Install system dependencies + Nginx
RUN apt-get update && apt-get install -y \
	nginx \
	libpq-dev \
	curl \
	&& docker-php-ext-install pdo pdo_pgsql \
	&& rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Copy entire application
COPY . /app

# Ensure database directory exists (in case COPY didn't copy empty dirs)
RUN mkdir -p /app/database /app/logs

# Copy Nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Make start script executable
RUN chmod +x /app/start.sh

# Set PHP-FPM to listen on TCP instead of socket for better Docker compatibility
RUN echo "listen = 9000" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "listen.backlog = 512" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_children = 20" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.start_servers = 2" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.min_spare_servers = 1" >> /usr/local/etc/php-fpm.d/www.conf && \
    echo "pm.max_spare_servers = 3" >> /usr/local/etc/php-fpm.d/www.conf

# Setup log files
RUN touch /var/log/php-fpm.log && \
    chmod 755 /app/logs

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:80/ || exit 1

# Expose port (Railway uses PORT environment variable)
EXPOSE 80

# Start Nginx + PHP-FPM
CMD ["/app/start.sh"]
