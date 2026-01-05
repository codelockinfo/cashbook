<?php
$pageTitle = 'Security';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security - Bookify</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #536dfe; 
            --primary-dark: #3d5afe;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        
        body { background: var(--bg-light); color: var(--text-dark); line-height: 1.6; }
        
        .navbar {
            background: #4a148c;
            padding: 20px 5%;
            display: flex; justify-content: space-between; align-items: center;
            color: white;
        }
        
        .logo { font-size: 24px; font-weight: 700; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; }
        .logo img { height: 32px; }
        
        .container {
            max-width: 800px; margin: 60px auto; padding: 40px;
            background: white; border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        h1 { font-size: 32px; margin-bottom: 25px; color: #111827; }
        
        h2 { font-size: 20px; margin-top: 30px; margin-bottom: 15px; color: #374151; }
        p, ul { margin-bottom: 15px; color: #4b5563; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
        
        .footer {
            text-align: center; padding: 40px; color: var(--text-light); font-size: 14px;
            border-top: 1px solid #e5e7eb;
        }
        
        .back-link {
            display: inline-block; margin-bottom: 20px; color: var(--primary); text-decoration: none; font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }

        .security-badge {
            display: inline-flex; align-items: center; gap: 10px;
            background: #ecfdf5; color: #059669;
            padding: 8px 16px; border-radius: 50px;
            font-weight: 600; font-size: 14px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">
            <img src="icons/bookify logo.png" alt="B" onerror="this.style.display='none'"> Bookify
        </a>
    </nav>

    <div class="container">
        <a href="index.php" class="back-link">&larr; Back to Home</a>
        
        <div>
            <span class="security-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Bank-Grade Security
            </span>
        </div>
        
        <h1>Security</h1>

        <p>Your data security is our top priority. We implement strong measures to protect your information.</p>

        <h2>1. Data Protection</h2>
        <ul>
            <li>Secure storage of all records</li>
            <li>Controlled access to user data</li>
            <li>Protection against unauthorized access</li>
        </ul>

        <h2>2. User Safety</h2>
        <ul>
            <li>Password-protected access (if enabled)</li>
            <li>Session security to prevent unauthorized usage</li>
            <li>Regular system monitoring</li>
        </ul>

        <h2>3. No Unauthorized Access</h2>
        <p>We do not access, modify, or share your business data without permission.</p>

        <h2>4. Best Practices</h2>
        <p>Users are encouraged to:</p>
        <ul>
            <li>Use strong passwords</li>
            <li>Keep their device secure</li>
            <li>Log out from shared devices</li>
        </ul>

        <h2>5. Security Updates</h2>
        <p>We continuously improve security features to protect against new threats.</p>
    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> Bookify. All rights reserved.
    </div>

</body>
</html>
