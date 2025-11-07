<?php
// Get base path from config if available, otherwise auto-detect
$basePath = defined('BASE_PATH') ? BASE_PATH : rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!-- PWA Meta Tags -->
<meta name="theme-color" content="#667eea">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Cash Book">
<meta name="application-name" content="Cash Book">
<meta name="msapplication-TileColor" content="#667eea">
<meta name="msapplication-tap-highlight" content="no">

<!-- Manifest -->
<link rel="manifest" href="<?php echo $basePath; ?>/manifest.json">

<!-- Apple Touch Icons -->
<link rel="apple-touch-icon" sizes="152x152" href="<?php echo $basePath; ?>/icons/icon-152x152.png">
<link rel="apple-touch-icon" sizes="192x192" href="<?php echo $basePath; ?>/icons/icon-192x192.png">
<link rel="apple-touch-icon" sizes="512x512" href="<?php echo $basePath; ?>/icons/icon-512x512.png">

<!-- Favicon -->
<link rel="icon" type="image/png" sizes="32x32" href="<?php echo $basePath; ?>/icons/icon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php echo $basePath; ?>/icons/icon-16x16.png">

<!-- iOS Splash Screens (optional) -->
<link rel="apple-touch-startup-image" href="<?php echo $basePath; ?>/icons/icon-512x512.png">

<script>
// Pass base path to JavaScript
window.BASE_PATH = '<?php echo $basePath; ?>';
</script>

