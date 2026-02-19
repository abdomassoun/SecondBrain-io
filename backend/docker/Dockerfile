# syntax = docker/dockerfile:1.2
# Base stage
FROM dunglas/frankenphp:1.10.1-builder-php8.3-bookworm AS base

# Install packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    bind9-utils \
    mycli \
    nodejs \
    npm \
    nano \
    uuid-runtime \ 
    libjpeg-dev \
    libpng-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip 

# Install PHP Extensions
RUN install-php-extensions \
  pdo_pgsql \
  exif \
  mbstring \
  gd \
  bcmath \
  redis \
  intl \
  zip \
  gmp \
  apcu \
  opcache \
  memcached \
  imagick \
  calendar \
  sockets \
  pcntl \
  @composer

# Force enable PDO PostgreSQL for all PHP SAPIs
RUN echo "extension=pdo_pgsql.so" > /usr/local/etc/php/conf.d/99-pdo_pgsql.ini

# Update PHP configurations
ENV PHP_MEMORY_LIMIT=1024M
RUN sed -e 's/^expose_php.*/expose_php = Off/' "$PHP_INI_DIR/php.ini-production" > "$PHP_INI_DIR/php.ini" \
    && sed -i -e 's/^upload_max_filesize.*/upload_max_filesize = 600M/' \
    -e 's/^post_max_size.*/post_max_size = 0/' \
    -e "s/^memory_limit.*/memory_limit = ${PHP_MEMORY_LIMIT}/" \
    -e 's/^max_execution_time.*/max_execution_time = 300/' "$PHP_INI_DIR/php.ini"

# Install global node modules
RUN npm install -g chokidar pnpm ember-cli npm-cli-login

# Install ssm-parent
COPY --from=ghcr.io/springload/ssm-parent:1.8 /usr/bin/ssm-parent /sbin/ssm-parent

# Create the pnpm directory and set the PNPM_HOME environment variable
RUN mkdir -p ~/.pnpm
ENV PNPM_HOME=/root/.pnpm

# Add the pnpm global bin to the PATH
ENV PATH=/root/.pnpm/bin:$PATH

# Set some build ENV variables
ENV LOG_CHANNEL=stdout
ENV CACHE_DRIVER=null
ENV BROADCAST_DRIVER=socketcluster
ENV QUEUE_CONNECTION=redis
ENV CADDYFILE_PATH=/backend/Caddyfile
ENV CONSOLE_PATH=/fleetbase/console
ENV OCTANE_SERVER=frankenphp

# Set environment
ARG ENVIRONMENT=production
ENV APP_ENV=$ENVIRONMENT

# Setup github auth
ARG GITHUB_AUTH_KEY

# Copy Caddyfile
COPY --chown=www-data:www-data ./Caddyfile $CADDYFILE_PATH

# Create /fleetbase directory and set correct permissions
RUN mkdir -p /backend-app/backend && mkdir -p /backend-app/console && chown -R www-data:www-data /backend-app

# Set working directory
WORKDIR /backend-app/backend

# If GITHUB_AUTH_KEY is provided, create auth.json with it
RUN if [ -n "$GITHUB_AUTH_KEY" ]; then echo "{\"github-oauth\": {\"github.com\": \"$GITHUB_AUTH_KEY\"}}" > auth.json; fi

# Prepare composer cache directory
RUN mkdir -p /var/www/.cache/composer && chown -R www-data:www-data /var/www/.cache/composer

# Optimize Composer Dependency Installation
COPY --chown=www-data:www-data ./backend/composer.json ./backend/composer.lock /backend-app/backend/

# Pre-install Composer dependencies
RUN su www-data -s /bin/sh -c "composer install --no-scripts --optimize-autoloader --no-dev --no-cache"

# Setup application
COPY --chown=www-data:www-data ./backend /backend-app/backend

# Re-run composer install to ensure vendor is complete
RUN su www-data -s /bin/sh -c "composer install --optimize-autoloader --no-cache"

# Dump autoload
RUN su www-data -s /bin/sh -c "composer dumpautoload"

# Setup composer root directory
RUN mkdir -p /root/.composer
RUN mkdir -p /backend-app/backend/.composer && chown www-data:www-data /backend-app/backend/.composer

# Setup logging
RUN mkdir -p /backend-app/backend/storage/logs/ && touch /backend-app/backend/storage/logs/laravel-$(date +'%Y-%m-%d').log
RUN chown -R www-data:www-data /backend-app/backend/storage
RUN chmod -R 755 /backend-app/backend/storage

# Set permissions for deploy script
RUN chmod +x /backend-app/backend/deploy.sh

# Install go-crond
RUN curl -L https://github.com/webdevops/go-crond/releases/download/23.12.0/go-crond.linux.amd64 > /usr/local/bin/go-crond && chmod +x /usr/local/bin/go-crond
COPY backend/docker/crontab ./crontab
RUN chmod 0600 ./crontab

# Scheduler dev stage
# FROM base AS scheduler-dev
# ENTRYPOINT []
# CMD ["go-crond", "--verbose", "root:./crontab"]

# # Scheduler stage
# FROM base AS scheduler
# ENTRYPOINT ["/sbin/ssm-parent", "-c", ".ssm-parent.yaml", "run", "--"]
# CMD ["go-crond", "--verbose", "root:./crontab"]

# # Events stage
# FROM base As events
# COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
# RUN chmod +x /usr/local/bin/entrypoint.sh
# ENTRYPOINT ["/sbin/ssm-parent", "-c", ".ssm-parent.yaml", "run", "--", "/usr/local/bin/entrypoint.sh"]
# CMD ["php", "artisan", "queue:work"]

# Events stage
# FROM base AS events-dev
# ENTRYPOINT []
# CMD ["php", "artisan", "queue:work"]

# Application dev stage
FROM base AS app-dev
COPY ./backend/docker/entrypoint-dev.sh /usr/local/bin/entrypoint-dev.sh
RUN chmod +x /usr/local/bin/entrypoint-dev.sh
ENTRYPOINT ["/usr/local/bin/entrypoint-dev.sh"]
CMD ["php", "artisan", "octane:frankenphp", "--max-requests=250", "--port=8000", "--host=0.0.0.0", "--watch"]

# Application release stage
# FROM base AS app-release
# ENTRYPOINT ["docker-php-entrypoint"]
# # Add --watch flag later
# CMD ["sh", "-c", "php artisan octane:frankenphp --workers=6 --max-requests=250 --port=8000 --host=0.0.0.0 --caddyfile $CADDYFILE_PATH"] 

# Application stage
FROM base as app
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/sbin/ssm-parent", "-c", ".ssm-parent.yaml", "run", "--", "/usr/local/bin/entrypoint.sh"]
CMD ["sh", "-c", "php artisan octane:frankenphp --workers=8 --max-requests=500 --port=8000 --host=0.0.0.0 --https --http-redirect --caddyfile $CADDYFILE_PATH"]
