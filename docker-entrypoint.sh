#!/bin/bash
set -e

# Start MySQL without authentication
start_mysql() {
    echo "🔵 Starting MySQL without authentication..."
    
    # Set up directories
    mkdir -p /var/lib/mysql /var/run/mysqld
    chown -R mysql:mysql /var/lib/mysql /var/run/mysqld
    chmod 777 /var/run/mysqld
    
    # Initialize MySQL if needed
    if [ ! -d "/var/lib/mysql/mysql" ]; then
        echo "🔄 Initializing MySQL data directory..."
        mysqld --initialize-insecure --user=mysql
    fi
    
    # Start MySQL with skip-grant-tables
    echo "🚀 Starting MySQL with skip-grant-tables..."
    mysqld_safe --skip-grant-tables --skip-networking > /dev/null 2>&1 &
    
    # Wait for MySQL to start
    echo "⏳ Waiting for MySQL to start..."
    for i in {1..30}; do
        if mysql -e "SELECT 1" >/dev/null 2>&1; then
            echo -e "\n✅ MySQL is running"
            return 0
        fi
        sleep 1
        echo -n "."
    done
    
    echo -e "\n❌ Failed to start MySQL"
    return 1
}

# Start MySQL
if ! start_mysql; then
    exit 1
fi

# Create database
echo "🔄 Setting up database..."
mysql -e "CREATE DATABASE IF NOT EXISTS banking;"

# Set up .env file
if [ ! -f .env ]; then
    echo "📄 Creating .env file..."
    cp .env.example .env
fi

# Update .env for no authentication
echo "🔄 Updating .env file..."
cat > .env <<EOL
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=banking
DB_USERNAME=
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Add any other required environment variables here
EOL

# Generate app key if needed
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Wait a bit more to ensure MySQL is fully up
echo "⏳ Waiting for MySQL to be ready for connections..."
sleep 5

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Clear caches
php artisan config:clear
php artisan cache:clear

# Start services
echo "🚀 Starting services..."
exec supervisord -n -c /etc/supervisor/supervisord.conf