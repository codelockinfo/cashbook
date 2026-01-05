<?php
$pageTitle = 'Privacy Policy';
require_once 'pwa-meta.php'; // Reuse meta tags if available, or just keep simple
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Bookify</title>
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
        
        h1 { font-size: 32px; margin-bottom: 10px; color: #111827; }
        .date { color: var(--text-light); margin-bottom: 40px; display: block; }
        
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
        
        <h1>Privacy Policy</h1>
        <span class="date">Effective Date: <?php echo date('F d, Y'); ?></span>

        <p>Your privacy is very important to us. This Privacy Policy explains how our Cashbook App collects, uses, and protects your information when you use our application.</p>

        <h2>1. Information We Collect</h2>
        <p>We may collect the following types of information:</p>
        <ul>
            <li><strong>Personal Information:</strong> Name, email address, phone number (only if provided by you).</li>
            <li><strong>Business Data:</strong> Income, expenses, transaction details, business names, and notes entered by you.</li>
            <li><strong>Device Information:</strong> Device type, operating system, app version, and basic usage data.</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>We use the collected information to:</p>
        <ul>
            <li>Provide and improve app functionality</li>
            <li>Maintain accurate financial records</li>
            <li>Enable multi-business management</li>
            <li>Ensure app security and prevent misuse</li>
            <li>Communicate important updates (if applicable)</li>
        </ul>

        <h2>3. Data Storage</h2>
        <p>All data entered in the app is stored securely. We do not sell, trade, or rent your personal or business data to any third party.</p>

        <h2>4. Data Sharing</h2>
        <p>We do not share your data with third parties except:</p>
        <ul>
            <li>When required by law</li>
            <li>To protect legal rights or prevent fraud</li>
        </ul>

        <h2>5. User Control</h2>
        <p>You have full control over your data:</p>
        <ul>
            <li>Add, edit, or delete records anytime</li>
            <li>Delete your account and associated data (if applicable)</li>
        </ul>

        <h2>6. Children’s Privacy</h2>
        <p>Our app is not intended for children under the age of 13. We do not knowingly collect personal information from children.</p>

        <h2>7. Policy Updates</h2>
        <p>We may update this Privacy Policy from time to time. Changes will be reflected within the app or on our website.</p>

        <h2>8. Contact Us</h2>
        <p>If you have any questions about this Privacy Policy, please contact us at:<br>
        Email: support@bookify.com</p>
    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> Bookify. All rights reserved.
    </div>

</body>
</html>
