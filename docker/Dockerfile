FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    cron \
    libxml2-dev \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_mysql xml zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction || true

# Set up cron job for RSS fetching (every 30 minutes)
RUN echo "*/30 * * * * cd /var/www/html && php bin/fetch-feeds.php >> /var/log/cron.log 2>&1" > /etc/cron.d/rss-fetch \
    && chmod 0644 /etc/cron.d/rss-fetch \
    && crontab /etc/cron.d/rss-fetch \
    && touch /var/log/cron.log

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Startup script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]
