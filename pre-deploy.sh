#!/bin/bash
# Pre-Deployment Script for Hostinger
# This script removes files that would conflict with Git pull

echo "Pre-deployment: Cleaning up conflicting files..."

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

# Remove cleanup script if it exists (will be pulled from Git)
if [ -f "cleanup-for-deploy.php" ]; then
    rm -f cleanup-for-deploy.php
    echo "✓ Removed cleanup-for-deploy.php (will be restored from Git)"
fi

echo "Pre-deployment cleanup completed!"

