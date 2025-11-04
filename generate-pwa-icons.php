<?php
/**
 * PWA Icon Generator
 * Generates app icons in various sizes for PWA
 * Run once: http://localhost/cashbook/generate-pwa-icons.php
 */

$iconsDir = __DIR__ . '/icons';

// Icon sizes needed for PWA
$sizes = [16, 32, 72, 96, 128, 144, 152, 192, 384, 512];

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Generate PWA Icons</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
        }
        .success {
            background: #10b981;
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 15px 0;
        }
        .info {
            background: #f0f9ff;
            border: 2px solid #3b82f6;
            color: #1e40af;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 15px 0;
        }
        .error {
            background: #ef4444;
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 15px 0;
        }
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 10px 0 0;
            cursor: pointer;
            font-weight: 600;
        }
        .btn:hover {
            transform: scale(1.02);
        }
        .icon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .icon-item {
            text-align: center;
            padding: 10px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .icon-item img {
            width: 100%;
            height: auto;
            margin-bottom: 8px;
        }
        .icon-size {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 600;
        }
        pre {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            border: 1px solid #e5e7eb;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>📱 PWA Icon Generator</h1>";

try {
    // Create icons directory
    if (!file_exists($iconsDir)) {
        if (!mkdir($iconsDir, 0755, true)) {
            throw new Exception("Failed to create icons directory");
        }
        echo "<div class='success'>✓ Created 'icons' directory</div>";
    } else {
        echo "<div class='info'>ℹ️ 'icons' directory already exists</div>";
    }
    
    // Generate SVG icons for each size
    $generatedIcons = [];
    
    foreach ($sizes as $size) {
        $filename = "icon-{$size}x{$size}.png";
        $filepath = $iconsDir . '/' . $filename;
        
        // Create SVG icon
        $svg = generateIconSVG($size);
        
        // Save as PNG using GD library if available
        if (extension_loaded('gd')) {
            $image = imagecreatetruecolor($size, $size);
            
            // Fill with gradient background
            $purple = imagecolorallocate($image, 102, 126, 234);
            imagefilledrectangle($image, 0, 0, $size, $size, $purple);
            
            // Add white book icon (simplified)
            $white = imagecolorallocate($image, 255, 255, 255);
            $iconSize = $size * 0.6;
            $padding = ($size - $iconSize) / 2;
            
            // Draw simple book shape
            imagefilledrectangle($image, $padding, $padding, $size - $padding, $size - $padding, $white);
            
            // Add purple accent
            $darkPurple = imagecolorallocate($image, 118, 75, 162);
            $lineWidth = max(2, $size / 50);
            imagefilledrectangle($image, $padding + $iconSize/3, $padding, $padding + $iconSize/3 + $lineWidth, $size - $padding, $darkPurple);
            
            imagepng($image, $filepath);
            imagedestroy($image);
            
            $generatedIcons[] = $filename;
        } else {
            // Fallback: save SVG
            file_put_contents(str_replace('.png', '.svg', $filepath), $svg);
            $generatedIcons[] = str_replace('.png', '.svg', $filename);
        }
    }
    
    echo "<div class='success'>
        <strong>✅ Icons Generated Successfully!</strong><br>
        Created " . count($generatedIcons) . " icon files.
    </div>";
    
    // Display generated icons
    echo "<div class='info'>
        <strong>📸 Generated Icons:</strong>
        <div class='icon-grid'>";
    
    foreach ($generatedIcons as $icon) {
        $iconPath = 'icons/' . $icon;
        $size = str_replace(['icon-', 'x', '.png', '.svg'], '', $icon);
        $size = explode('x', $size)[0];
        echo "<div class='icon-item'>
            <img src='$iconPath' alt='$size'>
            <div class='icon-size'>{$size}px</div>
        </div>";
    }
    
    echo "</div></div>";
    
    echo "<div class='info'>
        <strong>🎨 Want Custom Icons?</strong><br>
        Replace the generated PNG files in the 'icons' folder with your own custom icons.<br>
        Recommended tool: <a href='https://www.pwabuilder.com/imageGenerator' target='_blank'>PWA Image Generator</a>
    </div>";
    
    echo "<div class='success'>
        <strong>🎉 PWA Setup Complete!</strong><br><br>
        Your Cash Book app is now installable as a PWA!<br><br>
        
        <strong>📱 To Install:</strong><br>
        1. Open on mobile device<br>
        2. Look for 'Add to Home Screen' prompt<br>
        3. Click Install<br>
        4. App appears on home screen!
    </div>";
    
    echo "<div style='margin-top: 30px;'>
        <a href='dashboard.php' class='btn'>Go to Dashboard</a>
        <a href='login.php' class='btn' style='background: #6b7280;'>Go to Login</a>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <strong>✗ Error:</strong><br>
        " . $e->getMessage() . "
    </div>";
}

echo "    </div>
</body>
</html>";

// Function to generate SVG icon
function generateIconSVG($size) {
    return '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $size . ' ' . $size . '" width="' . $size . '" height="' . $size . '">
    <defs>
        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="' . $size . '" height="' . $size . '" fill="url(#grad1)"/>
    <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="' . ($size * 0.5) . '" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="middle">₹</text>
</svg>';
}
?>

