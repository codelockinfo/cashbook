-- Migration script to add profile picture functionality
-- Run this script if you already have the cashbook database set up

    USE cash_book;

    -- Add profile_picture column to users table
    ALTER TABLE users 
    ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER password;

    -- Success message
    SELECT 'Profile picture column added successfully!' AS message;

