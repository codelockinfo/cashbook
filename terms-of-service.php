<?php
$pageTitle = 'Terms of Service';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - Bookify</title>
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
        
        <h1>Terms of Service</h1>
        <span class="date">Effective Date: <?php echo date('F d, Y'); ?></span>

        <p>By using our Cashbook App, you agree to comply with and be bound by the following Terms of Service.</p>

        <h2>1. App Usage</h2>
        <ul>
            <li>The app is designed for personal and business expense tracking.</li>
            <li>You agree to use the app only for lawful purposes.</li>
            <li>You are responsible for the accuracy of the data you enter.</li>
        </ul>

        <h2>2. User Responsibility</h2>
        <ul>
            <li>Keep your login credentials secure.</li>
            <li>Do not attempt to misuse, hack, or disrupt the app.</li>
            <li>We are not responsible for financial decisions made based on app data.</li>
        </ul>

        <h2>3. Data Ownership</h2>
        <ul>
            <li>All data entered into the app belongs to you.</li>
            <li>We do not claim ownership over your business or financial data.</li>
        </ul>

        <h2>4. Service Availability</h2>
        <ul>
            <li>We strive to keep the app available at all times.</li>
            <li>Temporary downtime may occur due to maintenance or technical issues.</li>
        </ul>

        <h2>5. Limitation of Liability</h2>
        <p>We are not liable for:</p>
        <ul>
            <li>Data loss due to device failure or user actions</li>
            <li>Business losses resulting from incorrect entries</li>
            <li>Indirect or incidental damages</li>
        </ul>

        <h2>6. Termination</h2>
        <p>We reserve the right to suspend or terminate access if users violate these terms or misuse the app.</p>

        <h2>7. Changes to Terms</h2>
        <p>Terms may be updated periodically. Continued use of the app means acceptance of updated terms.</p>

        <h2>8. Governing Law</h2>
        <p>These Terms are governed by the laws applicable in India.</p>
    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> Bookify. All rights reserved.
    </div>

</body>
</html>
