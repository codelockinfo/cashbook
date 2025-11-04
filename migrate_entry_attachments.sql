-- Migration script to add attachment support to entries
-- Run this script if you already have the cashbook database set up

USE cash_book;

-- Add attachment column to entries table
ALTER TABLE entries 
ADD COLUMN attachment VARCHAR(255) DEFAULT NULL AFTER message;

-- Success message
SELECT 'Attachment column added to entries table successfully!' AS message;

