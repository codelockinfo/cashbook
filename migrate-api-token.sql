-- Migration script to add api_token column to users table
-- Run this once to add the api_token column

ALTER TABLE `users` 
ADD COLUMN `api_token` VARCHAR(64) NULL DEFAULT NULL AFTER `password`,
ADD INDEX `idx_api_token` (`api_token`(64));

-- Note: api_token will be generated on login and stored here
-- Token format: 64-character hexadecimal string (32 bytes = 64 hex chars)

