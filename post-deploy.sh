#!/bin/bash
# Post-Deployment Script for Hostinger
# This script installs Composer dependencies after Git pull

echo "Post-deployment: Installing Composer dependencies..."

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    echo "⚠ composer.json not found, skipping Composer install"
    exit 0
fi

# Try to use composer.phar if it exists
if [ -f "composer.phar" ]; then
    echo "Using composer.phar..."
    php composer.phar install --no-dev --optimize-autoloader
elif command -v composer &> /dev/null; then
    echo "Using system composer..."
    composer install --no-dev --optimize-autoloader
else
    echo "⚠ Composer not found. Downloading composer.phar..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php
    if [ -f "composer.phar" ]; then
        php composer.phar install --no-dev --optimize-autoloader
        echo "✓ Composer dependencies installed"
    else
        echo "✗ Failed to install Composer"
        exit 1
    fi
    rm -f composer-setup.php
fi

echo "Post-deployment completed!"

