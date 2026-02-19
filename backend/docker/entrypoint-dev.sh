#!/bin/sh
set -e

# Fix permissions for storage and bootstrap cache directories
echo "Fixing permissions..."
mkdir -p /backend-app/backend/storage/logs
mkdir -p /backend-app/backend/storage/framework/cache
mkdir -p /backend-app/backend/bootstrap/cache
chown -R www-data:www-data /backend-app/backend/storage
chown -R www-data:www-data /backend-app/backend/bootstrap/cache
chmod -R 775 /backend-app/backend/storage
chmod -R 775 /backend-app/backend/bootstrap/cache

# Run composer install to ensure all dependencies are available
echo "Installing composer dependencies..."
su www-data -s /bin/sh -c "composer install --no-interaction --optimize-autoloader"

# Execute the main command
exec "$@"
