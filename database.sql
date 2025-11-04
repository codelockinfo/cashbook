-- Create database
CREATE DATABASE IF NOT EXISTS cash_book CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cash_book;

-- Create users table (updated for authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email(191)),
    INDEX idx_name (name(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create groups table
CREATE TABLE IF NOT EXISTS groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_created_by (created_by),
    INDEX idx_name (name(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create group_members table
CREATE TABLE IF NOT EXISTS group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin', 'member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_group_user (group_id, user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create group_requests table
CREATE TABLE IF NOT EXISTS group_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    invited_by INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_group_id (group_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create entries table (updated for group support)
CREATE TABLE IF NOT EXISTS entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id INT NULL,
    type ENUM('in', 'out') NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    datetime DATETIME NOT NULL,
    message TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_created_by (created_by),
    INDEX idx_type (type),
    INDEX idx_datetime (datetime),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert demo users with authentication (password: demo123)
INSERT INTO users (name, email, password) VALUES
('Admin User', 'admin@cashbook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Tushar Rathod', 'tushar@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Rajan Zala', 'rajan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Amit Shah', 'amit@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Priya Patel', 'priya@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert demo groups
INSERT INTO groups (name, description, created_by) VALUES
('Office Team', 'Main office team for daily operations', 1),
('Project Alpha', 'Project Alpha development team', 2),
('Marketing', 'Marketing and sales team', 1);

-- Insert group members
INSERT INTO group_members (group_id, user_id, role) VALUES
(1, 1, 'admin'),
(1, 2, 'member'),
(1, 3, 'member'),
(2, 2, 'admin'),
(2, 4, 'member'),
(3, 1, 'admin'),
(3, 5, 'member');

-- Insert sample entries for demonstration
INSERT INTO entries (user_id, group_id, type, amount, datetime, message, created_by) VALUES
(2, 1, 'in', 5000.00, '2025-11-05 10:30:00', 'Payment received for Order 001', 2),
(2, 1, 'out', 2500.00, '2025-11-05 14:15:00', 'Office supplies purchase', 2),
(3, 1, 'in', 10000.00, '2025-11-04 09:00:00', 'Client advance payment', 3),
(4, 2, 'out', 3500.00, '2025-11-04 16:45:00', 'Vendor payment for materials', 4),
(4, 2, 'in', 7500.00, '2025-11-03 11:20:00', 'Project milestone payment', 4),
(5, 3, 'out', 1500.00, '2025-11-03 13:30:00', 'Travel expenses reimbursement', 5),
(2, 1, 'out', 4000.00, '2025-11-02 10:00:00', 'Monthly rent payment', 2),
(3, 1, 'in', 15000.00, '2025-11-02 15:00:00', 'Final project payment from client', 3),
(5, 3, 'out', 2000.00, '2025-11-01 09:30:00', 'Marketing expenses', 5),
(4, 2, 'in', 8000.00, '2025-11-01 14:00:00', 'Consulting service payment', 4);

-- Note: All demo users have password: demo123

