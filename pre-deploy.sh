#!/bin/bash
# Pre-Deployment Script for Hostinger
# This script removes files that would conflict with Git pull

echo "Pre-deployment: Removing vendor directory and composer.lock..."

# Remove vendor directory if it exists
if [ -d "vendor" ]; then
    rm -rf vendor
    echo "✓ Removed vendor directory"
fi

# Remove composer.lock if it exists
if [ -f "composer.lock" ]; then
    rm -f composer.lock
    echo "✓ Removed composer.lock"
fi

echo "Pre-deployment cleanup completed!"

