#!/bin/bash
# Hostinger Deployment Script
# This script handles Git deployment with Composer dependencies

echo "Starting deployment..."

# Remove vendor directory and composer.lock if they exist (from previous deployment)
if [ -d "vendor" ]; then
    echo "Removing existing vendor directory..."
    rm -rf vendor
fi

if [ -f "composer.lock" ]; then
    echo "Removing existing composer.lock..."
    rm -f composer.lock
fi

# Pull latest code from Git
echo "Pulling latest code from Git..."
git pull origin main || git pull origin master

# Install/update Composer dependencies
if [ -f "composer.json" ]; then
    echo "Installing Composer dependencies..."
    if [ -f "composer.phar" ]; then
        php composer.phar install --no-dev --optimize-autoloader
    else
        # Try to use system composer if available
        composer install --no-dev --optimize-autoloader || php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php && php composer.phar install --no-dev --optimize-autoloader && rm composer-setup.php
    fi
fi

echo "Deployment completed successfully!"

