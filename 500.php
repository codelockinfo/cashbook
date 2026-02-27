<?php
// Custom 500 Error Page / Loader
session_start();

// Get the URL the user was trying to access so we can reload it
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connecting to Bookify...</title>
    <!-- Add theme color for PWA mapping -->
    <meta name="theme-color" content="#121826" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #818cf8;
            --bg-color: #0f172a; /* Dark sleek background */
            --text-main: #f8fafc;
            --text-secondary: #94a3b8;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
            overflow: hidden;
        }

        .loader-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: rgba(30, 41, 59, 0.4);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-width: 85%;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .spinner-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin-bottom: 25px;
        }

        .spinner {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-left-color: var(--primary-color);
            border-right-color: var(--secondary-color);
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        .spinner-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--primary-color);
            font-size: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 0.6; transform: translate(-50%, -50%) scale(0.9); }
            50% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
        }

        h2 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #fff, var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.5;
            max-width: 250px;
        }
        
        .timer-bar-container {
            width: 100%;
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin-top: 25px;
            overflow: hidden;
            position: relative;
        }
        
        .timer-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 4px;
            animation: progress 4s linear infinite;
        }
        
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

    </style>

    <script>
        // Set a timeout to automatically refresh the page to the target URL
        // If the server was temporarily sleeping (like on shared hosting),
        // it will wake up during this loader and magically load the correct page!
        const targetUrl = <?php echo json_encode($request_uri); ?>;
        
        setTimeout(function() {
            // Replace the URL cleanly and forcefully bypass cache to restart PHP worker
            window.location.replace(targetUrl);
        }, 4000); // Wait exactly 4 seconds for the server worker to completely spin up
    </script>
</head>
<body>

    <div class="loader-wrapper">
        <div class="spinner-container">
            <div class="spinner"></div>
            <i class="fas fa-server spinner-icon"></i>
        </div>
        
        <h2>Waking up Bookify...</h2>
        
        <div class="timer-bar-container">
            <div class="timer-bar"></div>
        </div>
    </div>

</body>
</html>
