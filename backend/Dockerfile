FROM php:8.4-cli

# Install system dependencies including MongoDB driver dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    libssl-dev \
    libcurl4-openssl-dev \
    pkg-config \
    libsasl2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install MongoDB extension for PHP 8.3
RUN pecl channel-update pecl.php.net && \
    pecl install mongodb-1.20.1 && \
    docker-php-ext-enable mongodb

# Install other PHP extensions
RUN docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Configure PHP
RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini \
    && echo "upload_max_filesize=50M" > /usr/local/etc/php/conf.d/upload-limits.ini \
    && echo "post_max_size=50M" >> /usr/local/etc/php/conf.d/upload-limits.ini

# Copy ALL files including vendor/ from host
COPY . .

# Copy entrypoint script
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create JWT directory
RUN mkdir -p config/jwt

# Expose port
EXPOSE 8000

# Use entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
