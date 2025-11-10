<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWA Test - Cash Book</title>
    <?php include 'pwa-meta.php'; ?>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #10b981; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #ef4444; }
        .info { background: #dbeafe; color: #1e3a8a; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #3b82f6; }
        .warning { background: #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #f59e0b; }
        code { background: #1e293b; color: #e2e8f0; padding: 3px 8px; border-radius: 5px; }
        pre { background: #1e293b; color: #e2e8f0; padding: 20px; border-radius: 8px; overflow-x: auto; }
        .btn { background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; margin: 10px 5px; font-size: 16px; }
        .btn:hover { background: #5568d3; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h1>📱 PWA Configuration Test</h1>
        <p>Testing Progressive Web App setup for mobile install popup.</p>
        
        <h2>1. Environment Info</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Environment</td>
                <td><?php echo isLocalEnvironment() ? 'LOCAL' : 'LIVE'; ?></td>
            </tr>
            <tr>
                <td>BASE_PATH</td>
                <td><code><?php echo BASE_PATH === '' ? '(empty - at root)' : BASE_PATH; ?></code></td>
            </tr>
            <tr>
                <td>Current URL</td>
                <td><code><?php echo $_SERVER['REQUEST_URI']; ?></code></td>
            </tr>
        </table>
        
        <h2>2. PWA Files Check</h2>
        <div id="fileCheck"></div>
        <button onclick="checkPWAFiles()" class="btn">Check PWA Files</button>
        
        <h2>3. Service Worker Status</h2>
        <div id="swStatus"></div>
        <button onclick="checkServiceWorker()" class="btn">Check Service Worker</button>
        
        <h2>4. Install Prompt Test</h2>
        <div id="installStatus"></div>
        <button onclick="testInstallPrompt()" class="btn">Test Install Prompt</button>
        
        <h2>5. Manifest Test</h2>
        <div id="manifestStatus"></div>
        <button onclick="checkManifest()" class="btn">Check Manifest</button>
    </div>
    
    <div class="box">
        <h2>📋 PWA Requirements Checklist</h2>
        <div class="info">
            <strong>For PWA install popup to show on mobile, you need:</strong>
            <ol style="margin-left: 20px; margin-top: 10px; line-height: 2;">
                <li>✅ HTTPS (secure connection) - <em>Required on live, not on localhost</em></li>
                <li>✅ Valid manifest.json with icons</li>
                <li>✅ Service worker registered</li>
                <li>✅ User visits site at least once</li>
                <li>✅ User engages with site (30 seconds or more)</li>
                <li>✅ Not already installed</li>
            </ol>
        </div>
        
        <div class="warning">
            <strong>⚠️ iOS (iPhone/iPad) Note:</strong><br>
            iOS doesn't support the automatic install prompt. Users must manually:<br>
            1. Tap the Share button<br>
            2. Select "Add to Home Screen"
        </div>
    </div>
    
    <script src="<?php echo BASE_PATH; ?>/pwa7.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script>
        async function checkPWAFiles() {
            const resultDiv = document.getElementById('fileCheck');
            resultDiv.innerHTML = '<p>Checking PWA files...</p>';
            
            const files = [
                BASE_PATH + '/manifest.json.php',
                BASE_PATH + '/service-worker.js.php',
                BASE_PATH + '/pwa7.js',
                BASE_PATH + '/icons/icon-192x192.png',
                BASE_PATH + '/icons/icon-512x512.png'
            ];
            
            let html = '<table><tr><th>File</th><th>Status</th></tr>';
            
            for (const file of files) {
                try {
                    const response = await fetch(file, { method: 'HEAD' });
                    const status = response.ok ? 
                        '<span style="color: #10b981;">✓ OK (' + response.status + ')</span>' : 
                        '<span style="color: #ef4444;">✗ Error (' + response.status + ')</span>';
                    html += `<tr><td><code>${file}</code></td><td>${status}</td></tr>`;
                } catch (error) {
                    html += `<tr><td><code>${file}</code></td><td><span style="color: #ef4444;">✗ Failed</span></td></tr>`;
                }
            }
            
            html += '</table>';
            resultDiv.innerHTML = html;
        }
        
        function checkServiceWorker() {
            const resultDiv = document.getElementById('swStatus');
            
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistration()
                    .then((registration) => {
                        if (registration) {
                            resultDiv.innerHTML = `
                                <div class="success">
                                    ✓ Service Worker is registered<br>
                                    Scope: <code>${registration.scope}</code><br>
                                    State: <code>${registration.active ? 'Active' : 'Installing'}</code>
                                </div>
                            `;
                        } else {
                            resultDiv.innerHTML = `
                                <div class="warning">
                                    ⚠️ Service Worker not registered yet<br>
                                    Reload the page to register it.
                                </div>
                            `;
                        }
                    });
            } else {
                resultDiv.innerHTML = '<div class="error">✗ Service Workers not supported in this browser</div>';
            }
        }
        
        function testInstallPrompt() {
            const resultDiv = document.getElementById('installStatus');
            
            if (window.matchMedia('(display-mode: standalone)').matches) {
                resultDiv.innerHTML = '<div class="success">✓ App is already installed!</div>';
            } else if (deferredPrompt) {
                resultDiv.innerHTML = '<div class="success">✓ Install prompt is available! The popup should appear.</div>';
            } else {
                resultDiv.innerHTML = `
                    <div class="warning">
                        ⚠️ Install prompt not available yet<br><br>
                        <strong>Possible reasons:</strong><br>
                        • App already installed<br>
                        • Not on HTTPS (required on live sites)<br>
                        • Need to engage with site more (30+ seconds)<br>
                        • Browser doesn't support PWA (Safari/iOS)<br>
                        • Recently dismissed the prompt
                    </div>
                `;
            }
        }
        
        async function checkManifest() {
            const resultDiv = document.getElementById('manifestStatus');
            
            try {
                const response = await fetch(BASE_PATH + '/manifest.json.php');
                const manifest = await response.json();
                
                resultDiv.innerHTML = `
                    <div class="success">
                        ✓ Manifest loaded successfully<br><br>
                        <strong>App Name:</strong> ${manifest.name}<br>
                        <strong>Short Name:</strong> ${manifest.short_name}<br>
                        <strong>Start URL:</strong> <code>${manifest.start_url}</code><br>
                        <strong>Icons:</strong> ${manifest.icons.length} icons<br>
                        <strong>Scope:</strong> <code>${manifest.scope}</code>
                    </div>
                    <details>
                        <summary>View Full Manifest</summary>
                        <pre>${JSON.stringify(manifest, null, 2)}</pre>
                    </details>
                `;
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">✗ Failed to load manifest: ${error.message}</div>`;
            }
        }
        
        // Auto-run checks on load
        window.addEventListener('load', () => {
            checkPWAFiles();
            checkServiceWorker();
            checkManifest();
        });
    </script>
</body>
</html>

