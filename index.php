<?php
// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? strtolower($basePath) : '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    $sameSite = $isSecure ? 'None' : 'Lax';
    
    ini_set('session.gc_maxlifetime', 604800);
    session_set_cookie_params([
        'lifetime' => 604800,
        'path' => $cookiePath,
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => $sameSite
    ]);
    session_start();
}

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookify - Smart Finance Management System</title>
    <meta name="description" content="Bookify is the ultimate financial management solution for small businesses and freelancers. Track expenses, manage invoices, and grow your business with our smart finance tools.">
    <meta name="keywords" content="finance management, expense tracker, business finance, invoice app, bookkeeping software, small business tools, financial reports">
    <meta name="author" content="Bookify">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Bookify - Smart Finance Management System">
    <meta property="og:description" content="Run Your Business Finances Smarter. Manage income, expenses, invoices, assets, and more from one platform.">
    <meta property="og:image" content="icons/bookify%20logo.png">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="icons/bookify%20logo.png">
    <link rel="apple-touch-icon" href="icons/bookify%20logo.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary: #536dfe; 
            --primary-dark: #3d5afe;
            --secondary: #00e5ff; 
            --bg-gradient-start: #5e35b1;
            --bg-gradient-end: #311b92;
            --text-light: #ffffff;
            --text-dim: rgba(255, 255, 255, 0.8);
            --glass: rgba(255, 255, 255, 0.1);
            --glass-strong: rgba(255, 255, 255, 0.15);
            --glass-dark: rgba(0, 0, 0, 0.2);
            --border-white: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }

        body {
            background-color: #4a148c; /* Fallback */
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* --- Reusable Components --- */
        .section {
            padding: 100px 5%;
            position: relative;
        }

        .section-title {
            text-align: center;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(to right, #fff, #b3e5fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-subtitle {
            text-align: center;
            font-size: 18px;
            color: var(--text-dim);
            max-width: 700px;
            margin: 0 auto 60px;
            line-height: 1.6;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #536dfe; 
            color: white;
            box-shadow: 0 4px 15px rgba(83, 109, 254, 0.4);
        }

        .btn-primary:hover {
            background: #3d5afe;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(83, 109, 254, 0.5);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
        }

        .btn-outline:hover {
            background: white;
            color: var(--primary-dark);
            border-color: white;
        }
        
        .btn-white {
            background: white;
            color: var(--primary-dark);
        }
        
        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,255,255,0.2);
        }

        /* --- Backgrounds --- */
        .bg-gradient-main {
            background-color: #4a148c;
            background-image: 
                linear-gradient(180deg, rgba(88, 56, 178, 1) 0%, rgba(69, 39, 160, 1) 100%),
                radial-gradient(var(--border-white) 1px, transparent 1px);
            background-size: 100% 100%, 40px 40px;
        }

        .bg-dark {
            background: #311b92;
        }
        
        .bg-darker {
            background: #281577;
        }

        /* --- Top Bar & Navbar --- */
        .top-bar {
            background: linear-gradient(90deg, #5e35b1, #7e57c2);
            text-align: center;
            padding: 12px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            z-index: 100;
        }
        
        .top-bar a {
            color: white;
            text-decoration: underline;
            margin-left: 5px;
        }

        .top-bar .close-btn {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            opacity: 0.7;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 5%;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 90;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 28px;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .logo-img { height: 40px; width: 40px; object-fit: contain; }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-dim);
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover { color: white; }

        /* --- Hero Section --- */
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 80px 5% 120px;
            max-width: 1400px;
            margin: 0 auto;
            gap: 50px;
        }

        .hero-text { flex: 1; max-width: 650px; }

        .hero-text h1 {
            font-size: 64px;
            line-height: 1.1;
            font-weight: 700;
            margin-bottom: 24px;
            background: linear-gradient(to right, #fff, #b3e5fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 24px;
            color: #bdc5ff;
        }

        .hero-desc {
            font-size: 18px;
            line-height: 1.6;
            color: var(--text-dim);
            margin-bottom: 40px;
            max-width: 550px;
        }

        .hero-cta { display: flex; gap: 20px; margin-bottom: 50px; }
        
        .hero-cta .btn { padding: 14px 32px; font-size: 16px; border-radius: 12px; }

        .stats { display: flex; gap: 60px; }
        .stat-item h3 { font-size: 32px; font-weight: 800; margin-bottom: 5px; }
        .stat-item p { font-size: 14px; color: var(--text-dim); }

        /* Hero CSS Composition */
        .hero-image {
            flex: 1;
            position: relative;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .composition-container {
            position: relative;
            width: 100%;
            height: 100%;
            perspective: 1000px;
        }
        
        .card-glass {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            position: absolute;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
        }
        
        .main-card {
            width: 80%; height: 60%; top: 20%; left: 10%;
            transform: rotateY(-10deg) rotateX(5deg);
            padding: 24px; display: flex; flex-direction: column; gap: 15px;
            z-index: 2;
        }
        
        .skeleton-header { display: flex; gap: 15px; align-items: center; margin-bottom: 10px; }
        .circle { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); }
        .line { height: 12px; border-radius: 6px; background: rgba(255,255,255,0.1); flex: 1; }
        .line-short { width: 60px; }
        
        .chart-area {
            flex: 1;
            background: rgba(0,0,0,0.2); border-radius: 12px;
            position: relative; overflow: hidden;
            display: flex; align-items: flex-end; justify-content: space-around;
            padding: 0 10px 10px 10px;
        }
        
        .bar { width: 12%; background: linear-gradient(to top, #536dfe, #00e5ff); border-radius: 4px 4px 0 0; opacity: 0.8; }
        
        .widget-card-1 {
            width: 220px; height: 140px; top: 5%; right: 0;
            transform: translateZ(50px);
            z-index: 3; padding: 20px;
        }
        
        .widget-card-2 {
            width: 180px; height: 200px; bottom: 5%; left: 0;
            transform: translateZ(30px);
            z-index: 3; padding: 20px;
        }

        /* --- Features Section --- */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--glass);
            border: 1px solid var(--border-white);
            padding: 40px 30px;
            border-radius: 20px;
            transition: transform 0.3s;
        }

        .feature-card:hover { transform: translateY(-10px); background: var(--glass-strong); }

        .feature-icon {
            width: 60px; height: 60px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: white;
            margin-bottom: 24px;
        }

        .feature-title { font-size: 20px; font-weight: 700; margin-bottom: 12px; }
        .feature-desc { color: var(--text-dim); line-height: 1.6; }

        /* --- How It Works --- */
        .steps-container { max-width: 1000px; margin: 0 auto; }
        
        .step-item {
            display: flex; align-items: center; gap: 60px; margin-bottom: -210px;
        }
        
        .step-item:nth-child(even) { flex-direction: row-reverse; }
        
        .step-num {
            font-size: 80px; font-weight: 800; color: rgba(255,255,255,0.05);
            position: absolute; top: -40px; left: -20px; z-index: -1;
        }
        
        .step-content { flex: 1; position: relative; }
        .step-title { font-size: 28px; font-weight: 700; margin-bottom: 16px; }
        
        .step-visual {
            height: 700px;
            border-radius: 20px;
            border: 1px solid var(--border-white);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }

        .step-visual img {
            width: 100%;
            height: 100%;
        }

        /* --- Testimonials --- */
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .testimonial-card {
            background: white; color: #333;
            padding: 30px; border-radius: 16px;
        }
        
        .user-info { display: flex; align-items: center; gap: 15px; margin-top: 20px; }
        .user-avatar { width: 50px; height: 50px; background: #ddd; border-radius: 50%; }
        .user-name { font-weight: 700; font-size: 16px; }
        .user-role { font-size: 14px; color: #666; }
        .quote { font-style: italic; line-height: 1.6; color: #444; }

        /* --- FAQ --- */
        .faq-container { max-width: 800px; margin: 0 auto; }
        
        .faq-item {
            background: var(--glass);
            border: 1px solid var(--border-white);
            margin-bottom: 15px;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .faq-question {
            padding: 20px;
            font-weight: 600;
            cursor: pointer;
            display: flex; justify-content: space-between;
        }
        
        .faq-answer {
            padding: 0 20px 20px;
            color: var(--text-dim);
            line-height: 1.6;
            display: none; /* JS to toggle */
        }
        
        .faq-item.active .faq-answer { display: block; }

        /* --- CTA Band --- */
        .cta-band {
            background: var(--glass);
            text-align: center;
            border-radius: 30px;
            padding: 60px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .cta-band h2 { font-size: 36px; margin-bottom: 16px; color: white; }
        .cta-band p { margin-bottom: 30px; font-size: 18px; color: rgba(255,255,255,0.9); }

        /* --- Footer --- */
        footer {
            background: #1a0e4b;
            padding: 80px 5% 40px;
            margin-top: 0px;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 50px;
            max-width: 1400px;
            margin: 0 auto 60px;
        }
        
        .footer-col h4 { font-size: 18px; margin-bottom: 24px; color: white; }
        .footer-links-list a { display: block; color: var(--text-dim); text-decoration: none; margin-bottom: 12px; transition: color 0.3s; }
        .footer-links-list a:hover { color: var(--secondary); }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 40px;
            text-align: center;
            color: rgba(255,255,255,0.4);
            font-size: 14px;
        }

        /* --- Fixed Widgets --- */
        /* .fab-whatsapp {
            position: fixed; bottom: 30px; left: 30px;
            width: 60px; height: 60px;
            background: #25D366; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            cursor: pointer; z-index: 100; transition: transform 0.3s;
        }
        .fab-whatsapp:hover { transform: scale(1.1); } */

        .fab-get-started {
            position: fixed; bottom: 30px; right: 30px;
            background: white; color: var(--primary);
            padding: 12px 24px; border-radius: 50px;
            font-weight: 700;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            text-decoration: none;
            display: flex; align-items: center; gap: 10px;
            z-index: 100; transition: transform 0.3s;
        }
        .fab-get-started:hover { transform: translateY(-5px); }
        
        /* --- Mobile Navigation --- */
        .mobile-toggle {
            display: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
            z-index: 101;
        }

        @media (max-width: 968px) {
            .navbar { 
                justify-content: space-between; 
                padding: 15px 5%;
            }

            .mobile-toggle { display: block; }

            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                max-width: 300px;
                height: 100%;
                background: rgba(26, 14, 75, 0.98);
                backdrop-filter: blur(10px);
                flex-direction: column;
                justify-content: start;
                align-items: center;
                transition: right 0.4s ease;
                z-index: 99;
                box-shadow: -5px 0 30px rgba(0,0,0,0.5);
                padding:80px 40px;
            }

            .nav-links.active { right: 0; }
            
            .nav-links a { font-size: 20px; color: white; margin: 5px 0; }
            
            .auth-buttons { display: none; } /* Hide desktop auth buttons on mobile */
            .nav-links .mobile-auth { display: flex; flex-direction: column; gap: 15px; margin-top: 30px; width: 100%; }
            .nav-links .mobile-auth .btn { width: 100%; justify-content: center; }

            .hero { 
                flex-direction: column; 
                text-align: center; 
                padding-top: 40px; 
                padding-bottom: 60px;
                gap: 40px;
            }
            
            .hero-text { margin: 0 auto; display: flex; flex-direction: column; align-items: center; }
            
            .hero-text h1 { font-size: 42px; }
            
            .stats { flex-wrap: wrap; justify-content: center; gap: 30px; }
            
            .hero-image { 
                width: 100%; 
                height: 350px; 
                margin-top: 140px; /* Increased margin to prevent overlap */
                transform: scale(0.8); /* Reduced scale */
                margin-bottom: 80px;
            }

            .footer-grid { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 30px 20px; 
                text-align: left; 
                padding-bottom: 20px;
            }
            .footer-col:first-child { 
                grid-column: span 2; 
                text-align: center; 
                border-bottom: 1px solid rgba(255,255,255,0.1);
                padding-bottom: 20px;
                margin-bottom: 10px;
            }
            .footer-grid .logo-container { justify-content: center; }
            .footer-col p { margin: 0 auto; }
            .footer-col h4 { margin-bottom: 15px; } /* Compact header spacing */
            
            .step-item { flex-direction: column !important; gap: 30px; text-align: center; margin-bottom: 60px; }
            .step-num { left: 50%; transform: translateX(-50%); top: -50px; }
            .step-visual { height: 400px; }
            
            .cta-band { padding: 40px 20px; }
            .cta-band h2 { font-size: 28px; }
        }

        @media (max-width: 480px) {
            .section { padding: 60px 5%; }
            .hero-text h1 { font-size: 36px; }
            .hero-subtitle { font-size: 20px; }
            .section-title { font-size: 28px; }
            .stat-item h3 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <div class="bg-gradient-main">
        <!-- Top Bar -->
        <div class="top-bar">
            <?php echo date('Y'); ?> Special: Claim the professional plan on your first registration <a href="register">Click Here</a>
            <span class="close-btn" onclick="this.parentElement.style.display='none'">&times;</span>
        </div>

        <!-- Navbar -->
        <nav class="navbar">
            <a href="index.php" class="logo-container">
                <img src="icons/bookify logo.png" class="logo-img" alt="B" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJ3aGl0ZSIgc3Ryb2tlLXdpZHRoPSIyIj48cmVjdCB4PSI0IiB5PSI0IiB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHJ4PSIyIi8+PC9zdmc+'">
                <span>Bookify</span>
            </a>

            <div class="mobile-toggle" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>

            <div class="nav-links" id="navLinks">
                <a href="#features" onclick="toggleMenu()">Features</a>
                <a href="#how-it-works" onclick="toggleMenu()">How it Works</a>
                <a href="#testimonials" onclick="toggleMenu()">Testimonials</a>
                <a href="#faq" onclick="toggleMenu()">FAQ</a>
                
                <div class="mobile-auth">
                    <a href="register" class="btn btn-primary" onclick="toggleMenu()">
                        <i class="fas fa-rocket"></i> Register
                    </a>
                    <a href="login" class="btn btn-outline" onclick="toggleMenu()">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                    <a href="App/app-release.apk" class="btn btn-outline" download onclick="toggleMenu()">
                        <i class="fab fa-android"></i> Download App
                    </a>
                </div>
            </div>

            <div class="auth-buttons">
                <a href="App/app-release.apk" class="btn btn-primary" download>
                    <i class="fab fa-android"></i> Download App
                </a>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="hero">
            <div class="hero-text">
                <h1>Smart Finance <br>Management System</h1>
                <div class="hero-subtitle">Run Your Business Finances Smarter</div>
                <p class="hero-desc">
                    Manage income, expenses, invoices, assets, and more from one platform. 
                    Digitalize your finance tracking with our comprehensive management system.
                </p>

                <div class="hero-cta">
                    <a href="register" class="btn btn-white">
                        <i class="fas fa-rocket"></i> Register Your Business
                    </a>
                    <a href="login" class="btn btn-outline">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                </div>

                <div class="stats">
                    <div class="stat-item"><h3>500+</h3><p>Active Users</p></div>
                    <div class="stat-item"><h3>10k+</h3><p>Transactions</p></div>
                    <div class="stat-item"><h3>98%</h3><p>Satisfaction</p></div>
                </div>
            </div>

            <div class="hero-image">
                <div class="composition-container">
                    <div class="card-glass main-card">
                        <div class="skeleton-header"><div class="circle"></div><div class="line"></div></div>
                        <div class="line line-short"></div>
                        <div class="chart-area">
                            <div class="bar" style="height: 40%"></div>
                            <div class="bar" style="height: 70%"></div>
                            <div class="bar" style="height: 50%"></div>
                            <div class="bar" style="height: 85%"></div>
                            <div class="bar" style="height: 60%"></div>
                            <div class="bar" style="height: 90%"></div>
                        </div>
                        <div class="line"></div>
                    </div>
                    <div class="card-glass widget-card-1">
                        <div class="line" style="width: 50%; margin-bottom: 10px;"></div>
                        <div class="line" style="width: 80%; margin-bottom: 10px;"></div>
                        <div class="circle" style="float: right; margin-top: 10px;"></div>
                    </div>
                    <div class="card-glass widget-card-2">
                        <div class="chart-area" style="background: transparent;">
                            <div style="width: 80px; height: 80px; border: 10px solid #00e5ff; border-radius: 50%; margin: 20px auto; border-right-color: transparent;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </div>

    <!-- Features Section -->
    <section id="features" class="section bg-dark">
        <h2 class="section-title">Why Choose Bookify?</h2>
        <p class="section-subtitle">Everything you need to manage your business finances in one place.</p>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3 class="feature-title">Expense Tracking</h3>
                <p class="feature-desc">Monitor your spending in real-time. Categorize expenses and see where your money goes.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3 class="feature-title">Expense Management</h3>
                <p class="feature-desc">Easily track and manage your daily expenses with our smart Expense Management system.
Record income and expenses in real time, categorize transactions, and maintain a clear financial overview.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3 class="feature-title">Multi Business Handler</h3>
                <p class="feature-desc">Our Multi Business Handler feature allows you to manage multiple businesses or accounts from a single app.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                <h3 class="feature-title">Mobile Access</h3>
                <p class="feature-desc">Manage your business on the go. Access your dashboard from any device, anywhere.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-pie"></i></div>
                <h3 class="feature-title">Detailed Reports</h3>
                <p class="feature-desc">Gain insights with comprehensive financial reports and visual analytics.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-cloud-download-alt"></i></div>
                <h3 class="feature-title">Filtered Data</h3>
                <p class="feature-desc">Filter records by date, category, payment type, or amount to analyze transactions efficiently. This feature makes reporting and auditing faster and more accurate.</p>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="section bg-darker">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Get started with Bookify in three simple steps.</p>
        
        <div class="steps-container">
            <div class="step-item">
                <div class="step-content">
                    <div class="step-num">01</div>
                    <h3 class="step-title">Create an Account</h3>
                    <p class="feature-desc">Sign up in seconds. All you need is an email address. No credit card required for the free tier.</p>
                </div>
                <div class="step-visual">
                    <img src="assets/img/img1.png" alt="Step Visual">   
                </div>
            </div>
            
            <div class="step-item">
                <div class="step-content">
                    <div class="step-num">02</div>
                    <h3 class="step-title">Add Your Data</h3>
                    <p class="feature-desc">Input your income, expenses, and customer details. Import data easily from spreadsheets.</p>
                </div>
                <div class="step-visual">
                    <img src="assets/img/img2.png" alt="Step Visual">   
                </div>
            </div>
            
            <div class="step-item" style="margin-bottom: 10px;">
                <div class="step-content">
                    <div class="step-num">03</div>
                    <h3 class="step-title">Track & Grow</h3>
                    <p class="feature-desc">View your dashboard, generate reports, and make data-driven decisions to grow your business.</p>
                </div>
                <div class="step-visual">
                    <img src="assets/img/img3.png" alt="Step Visual">   
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="section" style="background: #f9f9f9; color: #333;">
        <h2 class="section-title" style="-webkit-text-fill-color: #333;">What Our Users Say</h2>
        <p class="section-subtitle" style="color: #666;">Trusted by thousands of small business owners.</p>
        
        <div class="testimonials-grid">
            <div class="testimonial-card">
                <p class="quote">"Bookify has completely transformed how I manage my shop's finances. It's so intuitive!"</p>
                <div class="user-info">
                    <div class="user-avatar">
                        <img src="assets/img/user1.jpg" alt="User Avatar" style="width: 50px; height: 50px; border-radius: 50%;">
                    </div>
                    <div>
                        <div class="user-name">Priyansh Joshi</div>
                        <div class="user-role">Retail shop's Owner</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="quote">"The reporting feature is a lifesaver. I can finally see where I'm losing money."</p>
                <div class="user-info">
                    <div class="user-avatar">
                        <img src="assets/img/user4.jpg" alt="User Avatar" style="width: 50px; height: 50px; border-radius: 50%;">
                    </div>
                    <div>
                        <div class="user-name">Dipak Kumar</div>
                        <div class="user-role">Freelance Designer</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="quote">"I love that I can access everything from my phone. Best financial decision I made this year."</p>
                <div class="user-info">
                    <div class="user-avatar" >
                        <img src="assets/img/user3.jpg" alt="User Avatar" style="width: 50px; height: 50px; border-radius: 50%;">
                    </div>
                    <div>
                        <div class="user-name">Rahul Mehra</div>
                        <div class="user-role">Consultant</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Band -->
    <section class="section bg-dark">
        <div class="cta-band">
            <h2>Ready to Take Control?</h2>
            <p>Join over 500+ businesses using Bookify to manage their finances.</p>
            <div style="display:flex; gap:15px; justify-content:center; flex-wrap:wrap;">
                <a href="register" class="btn btn-white" style="color:var(--primary); font-weight:800;">Get Started for Free</a>
                <a href="App/app-release.apk" class="btn btn-outline" download>Download App</a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="section bg-darker">
        <h2 class="section-title">Frequently Asked Questions</h2>
        
        <div class="faq-container">
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question"> Is Bookify really free? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-answer">Yes! We offer a generous free tier that includes all essential features. Premium plans are available for advanced needs.</div>
            </div>
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question"> Can I export my data? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-answer">Absolutely. You can export reports and transaction histories in PDF and CSV formats at any time.</div>
            </div>
            <div class="faq-item" onclick="this.classList.toggle('active')">
                <div class="faq-question"> Is my data secure? <i class="fas fa-chevron-down"></i></div>
                <div class="faq-answer">We use bank-level encryption (AES-256) to ensure your financial data remains private and secure.</div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-grid">
            <div class="footer-col">
                <a href="#" class="logo-container" style="margin-bottom: 20px;">
                    <img src="icons/bookify logo.png" class="logo-img" alt="B" onerror="this.style.display='none'">
                    <span>Bookify</span>
                </a>
                <p style="color: var(--text-dim); line-height: 1.6; max-width: 300px;">
                    The ultimate financial management solution for small businesses and freelancers. Simple, secure, and smart.
                </p>
            </div>
            
            <div class="footer-col">
                <h4>Product</h4>
                <div class="footer-links-list">
                    <a href="#features">Features</a>
                    <a href="#how-it-works">How it works</a>
                    <a href="#testimonials">Testimonials</a>
                </div>
            </div>
            
            <div class="footer-col">
                <h4>Company</h4>
                <div class="footer-links-list">
                    <a href="#">Blog</a>
                    <a href="#">Contact</a>
                    <a href="#download">Download App</a>    
                </div>
            </div>
            
            <div class="footer-col">
                <h4>Legal</h4>
                <div class="footer-links-list">
                    <a href="privacy-policy.php">Privacy Policy</a>
                    <a href="terms-of-service.php">Terms of Service</a>
                    <a href="security.php">Security</a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Bookify. All rights reserved.
        </div>
    </footer>

    <!-- Floating Widgets -->
    <!-- <div class="fab-whatsapp" onclick="window.open('https://wa.me/1234567890', '_blank')">
        <i class="fab fa-whatsapp"></i>
    </div> -->

    

    <!-- Mobile Menu Script -->
    <script>
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            const toggleIcon = document.querySelector('.mobile-toggle i');
            
            navLinks.classList.toggle('active');
            
            if (navLinks.classList.contains('active')) {
                toggleIcon.classList.remove('fa-bars');
                toggleIcon.classList.add('fa-times');
                document.body.style.overflow = 'hidden'; // Prevent scrolling
            } else {
                toggleIcon.classList.remove('fa-times');
                toggleIcon.classList.add('fa-bars');
                document.body.style.overflow = 'auto'; // Enable scrolling
            }
        }
    </script>
</body>
</html>
