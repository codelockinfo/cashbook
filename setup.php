<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Book - Database Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .setup-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .subtitle {
            color: #6b7280;
            margin-bottom: 30px;
        }
        
        .status {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .info {
            background: #dbeafe;
            color: #1e3a8a;
            border-left: 4px solid #3b82f6;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .step {
            padding: 15px;
            background: #f9fafb;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        
        .step-icon.pending {
            background: #9ca3af;
        }
        
        .step-icon.success {
            background: #10b981;
        }
        
        .step-icon.error {
            background: #ef4444;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-weight: 600;
            color: #111827;
        }
        
        .step-desc {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>📚 Cash Book Setup</h1>
        <p class="subtitle">Database Installation Wizard</p>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db_host = 'localhost';
            $db_user = 'root';
            $db_pass = '';
            $db_name = 'cash_book';
            
            echo '<div class="status info">Starting database setup...</div>';
            
            // Step 1: Connect to MySQL
            echo '<div class="step">';
            $conn = @new mysqli($db_host, $db_user, $db_pass);
            if ($conn->connect_error) {
                echo '<div class="step-icon error">✗</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 1: MySQL Connection - FAILED</div>';
                echo '<div class="step-desc">Error: ' . $conn->connect_error . '</div>';
                echo '</div></div>';
                exit;
            } else {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 1: MySQL Connection - SUCCESS</div>';
                echo '<div class="step-desc">Connected to MySQL server successfully</div>';
                echo '</div></div>';
            }
            
            // Step 2: Create Database
            echo '<div class="step">';
            $sql = "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            if ($conn->query($sql) === TRUE) {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 2: Create Database - SUCCESS</div>';
                echo '<div class="step-desc">Database "' . $db_name . '" created/verified</div>';
                echo '</div></div>';
            } else {
                echo '<div class="step-icon error">✗</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 2: Create Database - FAILED</div>';
                echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                echo '</div></div>';
                exit;
            }
            
            $conn->select_db($db_name);
            
            // Step 3: Create Users Table
            echo '<div class="step">';
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_email (email(191)),
                INDEX idx_name (name(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($conn->query($sql) === TRUE) {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 3: Create Users Table - SUCCESS</div>';
                echo '<div class="step-desc">Users table created successfully</div>';
                echo '</div></div>';
            } else {
                echo '<div class="step-icon error">✗</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 3: Create Users Table - FAILED</div>';
                echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                echo '</div></div>';
            }
            
            // Step 4: Create Groups Table
            echo '<div class="step">';
            $sql = "CREATE TABLE IF NOT EXISTS groups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                created_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
                INDEX idx_created_by (created_by),
                INDEX idx_name (name(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($conn->query($sql) === TRUE) {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 4: Create Groups Table - SUCCESS</div>';
                echo '<div class="step-desc">Groups table created successfully</div>';
                echo '</div></div>';
            } else {
                echo '<div class="step-icon error">✗</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 4: Create Groups Table - FAILED</div>';
                echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                echo '</div></div>';
            }
            
            // Step 5: Create Group Members Table
            echo '<div class="step">';
            $sql = "CREATE TABLE IF NOT EXISTS group_members (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($conn->query($sql) === TRUE) {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 5: Create Group Members Table - SUCCESS</div>';
                echo '<div class="step-desc">Group members table created successfully</div>';
                echo '</div></div>';
            } else {
                echo '<div class="step-icon error">✗</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 5: Create Group Members Table - FAILED</div>';
                echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                echo '</div></div>';
            }
            
            // Step 6: Create Group Requests Table
            echo '<div class="step">';
            $sql = "CREATE TABLE IF NOT EXISTS group_requests (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($conn->query($sql) === TRUE) {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 6: Create Group Requests Table - SUCCESS</div>';
                echo '<div class="step-desc">Group requests table created successfully</div>';
                echo '</div></div>';
            } else {
                echo '<div class="step-icon error">✗</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 6: Create Group Requests Table - FAILED</div>';
                echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                echo '</div></div>';
            }
            
            // Step 7: Create Entries Table
            echo '<div class="step">';
            $sql = "CREATE TABLE IF NOT EXISTS entries (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if ($conn->query($sql) === TRUE) {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 7: Create Entries Table - SUCCESS</div>';
                echo '<div class="step-desc">Entries table created successfully</div>';
                echo '</div></div>';
            } else {
                echo '<div class="step-icon error">✗</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 7: Create Entries Table - FAILED</div>';
                echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                echo '</div></div>';
            }
            
            // Step 8: Insert Sample Users
            echo '<div class="step">';
            $check = $conn->query("SELECT COUNT(*) as count FROM users");
            $row = $check->fetch_assoc();
            
            if ($row['count'] == 0) {
                $hashedPassword = password_hash('demo123', PASSWORD_DEFAULT);
                $users = [
                    ['Admin User', 'admin@cashbook.com'],
                    ['Tushar Rathod', 'tushar@example.com'],
                    ['Rajan Zala', 'rajan@example.com'],
                    ['Amit Shah', 'amit@example.com'],
                    ['Priya Patel', 'priya@example.com']
                ];
                
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $success = true;
                
                foreach ($users as $user) {
                    $stmt->bind_param("sss", $user[0], $user[1], $hashedPassword);
                    if (!$stmt->execute()) {
                        $success = false;
                        break;
                    }
                }
                
                if ($success) {
                    echo '<div class="step-icon success">✓</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 8: Insert Sample Users - SUCCESS</div>';
                    echo '<div class="step-desc">5 demo users added (password: demo123)</div>';
                    echo '</div></div>';
                } else {
                    echo '<div class="step-icon error">✗</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 8: Insert Sample Users - FAILED</div>';
                    echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                    echo '</div></div>';
                }
            } else {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 8: Sample Users - SKIPPED</div>';
                echo '<div class="step-desc">Users already exist (' . $row['count'] . ' users found)</div>';
                echo '</div></div>';
            }
            
            // Step 9: Insert Sample Groups
            echo '<div class="step">';
            $check = $conn->query("SELECT COUNT(*) as count FROM groups");
            $row = $check->fetch_assoc();
            
            if ($row['count'] == 0) {
                $sql = "INSERT INTO groups (name, description, created_by) VALUES
                ('Office Team', 'Main office team for daily operations', 1),
                ('Project Alpha', 'Project Alpha development team', 2),
                ('Marketing', 'Marketing and sales team', 1)";
                
                if ($conn->query($sql) === TRUE) {
                    echo '<div class="step-icon success">✓</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 9: Insert Sample Groups - SUCCESS</div>';
                    echo '<div class="step-desc">3 sample groups added</div>';
                    echo '</div></div>';
                } else {
                    echo '<div class="step-icon error">✗</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 9: Insert Sample Groups - FAILED</div>';
                    echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                    echo '</div></div>';
                }
            } else {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 9: Sample Groups - SKIPPED</div>';
                echo '<div class="step-desc">Groups already exist</div>';
                echo '</div></div>';
            }
            
            // Step 10: Insert Group Members
            echo '<div class="step">';
            $check = $conn->query("SELECT COUNT(*) as count FROM group_members");
            $row = $check->fetch_assoc();
            
            if ($row['count'] == 0) {
                $sql = "INSERT INTO group_members (group_id, user_id, role) VALUES
                (1, 1, 'admin'), (1, 2, 'member'), (1, 3, 'member'),
                (2, 2, 'admin'), (2, 4, 'member'),
                (3, 1, 'admin'), (3, 5, 'member')";
                
                if ($conn->query($sql) === TRUE) {
                    echo '<div class="step-icon success">✓</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 10: Insert Group Members - SUCCESS</div>';
                    echo '<div class="step-desc">7 group memberships added</div>';
                    echo '</div></div>';
                } else {
                    echo '<div class="step-icon error">✗</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 10: Insert Group Members - FAILED</div>';
                    echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                    echo '</div></div>';
                }
            } else {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 10: Group Members - SKIPPED</div>';
                echo '<div class="step-desc">Members already exist</div>';
                echo '</div></div>';
            }
            
            // Step 11: Insert Sample Entries
            echo '<div class="step">';
            $check = $conn->query("SELECT COUNT(*) as count FROM entries");
            $row = $check->fetch_assoc();
            
            if ($row['count'] == 0) {
                $sql = "INSERT INTO entries (user_id, group_id, type, amount, datetime, message, created_by) VALUES
                (2, 1, 'in', 5000.00, '2025-11-05 10:30:00', 'Payment received for Order 001', 2),
                (2, 1, 'out', 2500.00, '2025-11-05 14:15:00', 'Office supplies purchase', 2),
                (3, 1, 'in', 10000.00, '2025-11-04 09:00:00', 'Client advance payment', 3),
                (4, 2, 'out', 3500.00, '2025-11-04 16:45:00', 'Vendor payment for materials', 4),
                (4, 2, 'in', 7500.00, '2025-11-03 11:20:00', 'Project milestone payment', 4),
                (5, 3, 'out', 1500.00, '2025-11-03 13:30:00', 'Travel expenses reimbursement', 5),
                (2, 1, 'out', 4000.00, '2025-11-02 10:00:00', 'Monthly rent payment', 2),
                (3, 1, 'in', 15000.00, '2025-11-02 15:00:00', 'Final project payment from client', 3),
                (5, 3, 'out', 2000.00, '2025-11-01 09:30:00', 'Marketing expenses', 5),
                (4, 2, 'in', 8000.00, '2025-11-01 14:00:00', 'Consulting service payment', 4)";
                
                if ($conn->query($sql) === TRUE) {
                    echo '<div class="step-icon success">✓</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 11: Insert Sample Entries - SUCCESS</div>';
                    echo '<div class="step-desc">10 sample transactions added</div>';
                    echo '</div></div>';
                } else {
                    echo '<div class="step-icon error">✗</div>';
                    echo '<div class="step-content">';
                    echo '<div class="step-title">Step 11: Insert Sample Entries - FAILED</div>';
                    echo '<div class="step-desc">Error: ' . $conn->error . '</div>';
                    echo '</div></div>';
                }
            } else {
                echo '<div class="step-icon success">✓</div>';
                echo '<div class="step-content">';
                echo '<div class="step-title">Step 11: Sample Entries - SKIPPED</div>';
                echo '<div class="step-desc">Entries already exist (' . $row['count'] . ' entries found)</div>';
                echo '</div></div>';
            }
            
            $conn->close();
            
            echo '<div class="status success">✅ Database setup completed successfully!</div>';
            echo '<a href="login.php" class="link">→ Go to Login Page</a>';
        } else {
            ?>
            <div class="status info">
                This wizard will set up the database for your Cash Book application.
            </div>
            
            <div class="step">
                <div class="step-icon pending">1</div>
                <div class="step-content">
                    <div class="step-title">Connect to MySQL</div>
                    <div class="step-desc">Establish connection to your MySQL server</div>
                </div>
            </div>
            
            <div class="step">
                <div class="step-icon pending">2</div>
                <div class="step-content">
                    <div class="step-title">Create Database</div>
                    <div class="step-desc">Create the "cashbook" database</div>
                </div>
            </div>
            
            <div class="step">
                <div class="step-icon pending">3</div>
                <div class="step-content">
                    <div class="step-title">Create Tables</div>
                    <div class="step-desc">Create users and entries tables</div>
                </div>
            </div>
            
            <div class="step">
                <div class="step-icon pending">4</div>
                <div class="step-content">
                    <div class="step-title">Insert Sample Data</div>
                    <div class="step-desc">Add sample users and transactions for testing</div>
                </div>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn">🚀 Start Setup</button>
            </form>
            
            <p style="margin-top: 20px; color: #6b7280; font-size: 0.875rem;">
                <strong>Note:</strong> Make sure your MySQL server is running and accessible with the default credentials (root, no password).
            </p>
            <?php
        }
        ?>
    </div>
</body>
</html>

