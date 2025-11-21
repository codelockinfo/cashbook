-- Migration script to add status field and deletion tracking to entries table
-- Run this script if you already have the cashbook database set up
-- Status: 1 = Active (shown), 0 = Inactive (deleted - shown with blur)
-- deleted_at: When the entry was deleted
-- deleted_by: Which user deleted the entry
-- 
-- This script safely checks if columns exist before adding them
-- Safe to run on existing databases without losing data

USE cash_book;

-- Check and add status column (if not exists)
SET @status_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'entries' 
    AND COLUMN_NAME = 'status'
);

SET @sql_status = IF(@status_exists = 0,
    'ALTER TABLE entries ADD COLUMN status TINYINT(1) NOT NULL DEFAULT 1 AFTER created_at',
    'SELECT "Column status already exists, skipping..." AS message'
);
PREPARE stmt FROM @sql_status;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add deleted_at column (if not exists)
SET @deleted_at_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'entries' 
    AND COLUMN_NAME = 'deleted_at'
);

SET @sql_deleted_at = IF(@deleted_at_exists = 0,
    'ALTER TABLE entries ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER status',
    'SELECT "Column deleted_at already exists, skipping..." AS message'
);
PREPARE stmt FROM @sql_deleted_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add deleted_by column (if not exists)
SET @deleted_by_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'entries' 
    AND COLUMN_NAME = 'deleted_by'
);

SET @sql_deleted_by = IF(@deleted_by_exists = 0,
    'ALTER TABLE entries ADD COLUMN deleted_by INT NULL DEFAULT NULL AFTER deleted_at',
    'SELECT "Column deleted_by already exists, skipping..." AS message'
);
PREPARE stmt FROM @sql_deleted_by;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add foreign key constraint (if not exists)
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'entries' 
    AND CONSTRAINT_NAME = 'entries_ibfk_4'
);

SET @sql_fk = IF(@fk_exists = 0,
    'ALTER TABLE entries ADD CONSTRAINT `entries_ibfk_4` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL',
    'SELECT "Foreign key constraint entries_ibfk_4 already exists, skipping..." AS message'
);
PREPARE stmt FROM @sql_fk;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add index for status (if not exists)
SET @idx_status_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'entries' 
    AND INDEX_NAME = 'idx_status'
);

SET @sql_idx_status = IF(@idx_status_exists = 0,
    'ALTER TABLE entries ADD INDEX `idx_status` (`status`)',
    'SELECT "Index idx_status already exists, skipping..." AS message'
);
PREPARE stmt FROM @sql_idx_status;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add index for deleted_at (if not exists)
SET @idx_deleted_at_exists = (
    SELECT COUNT(*) 
    FROM INFORMATION_SCHEMA.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'entries' 
    AND INDEX_NAME = 'idx_deleted_at'
);

SET @sql_idx_deleted_at = IF(@idx_deleted_at_exists = 0,
    'ALTER TABLE entries ADD INDEX `idx_deleted_at` (`deleted_at`)',
    'SELECT "Index idx_deleted_at already exists, skipping..." AS message'
);
PREPARE stmt FROM @sql_idx_deleted_at;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing entries to have status = 1 if NULL (safety check)
UPDATE entries SET status = 1 WHERE status IS NULL;

-- Success message
SELECT 'Migration completed successfully! Columns and indexes checked/added. All existing entries are preserved.' AS message;

