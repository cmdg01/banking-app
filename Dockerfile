FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Copy environment file
COPY .env.example .env

# Install PHP dependencies
RUN composer install --no-interaction --no-scripts

# Modify .env for MySQL
RUN sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env && \
    sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=banking-db/' .env && \
    sed -i 's/# DB_PORT=3306/DB_PORT=3306/' .env && \
    sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=banking/' .env && \
    sed -i 's/# DB_USERNAME=root/DB_USERNAME=banking/' .env && \
    sed -i 's/# DB_PASSWORD=/DB_PASSWORD=banking_password/' .env

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html/storage

EXPOSE 9000

# We'll generate the key and run migrations after the container starts
CMD ["php-fpm"]
