#!/bin/sh

# Exit the script as soon as a command fails
set -e

# Copy environment file if it doesn't exist
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Create mysql databases if none exists
# php artisan mysql:createdb

# Run migrations
php artisan migrate --force

# Run migrations for sandbox too
# php artisan sandbox:migrate --force

# Seed database
php artisan db:seed

# Create permissions, policies, and roles
# php artisan fleetbase:create-permissions

# Restart queue
php artisan queue:restart

# Sync scheduler (commented out - package not installed)
# php artisan schedule-monitor:sync

# Clear cache
php artisan cache:clear
php artisan route:clear

# Optimize
php artisan config:cache
php artisan route:cache

# Initialize registry (commented out - package not installed)
# php artisan registry:init

# Restart octane
php artisan octane:reload
