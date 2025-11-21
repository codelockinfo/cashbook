-- Migration script to add entry edit history tracking
-- Run this script to track who edited entries and what fields were changed

USE cash_book;

-- Create entry_edit_history table to track all edits
CREATE TABLE IF NOT EXISTS `entry_edit_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry_id` int(11) NOT NULL,
  `edited_by` int(11) NOT NULL,
  `edited_at` datetime NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entry_id` (`entry_id`),
  KEY `idx_edited_by` (`edited_by`),
  KEY `idx_edited_at` (`edited_at`),
  CONSTRAINT `entry_edit_history_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entry_edit_history_ibfk_2` FOREIGN KEY (`edited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Success message
SELECT 'Entry edit history table created successfully!' AS message;

