FROM php:8.2-fpm-alpine

# Install system dependencies needed to compile PHP extensions
RUN apk add --no-cache \
    curl-dev \
    libxml2-dev \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    curl \
    mbstring \
    xml

# Set working directory
WORKDIR /var/www/html

# Copy application source
COPY . .

# Create cache/storage directory with correct permissions
RUN mkdir -p /var/www/html/storage/cache \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# Expose php-fpm port
EXPOSE 9000

CMD ["php-fpm"]
