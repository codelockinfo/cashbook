<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Path Test - Live Site</title>
    <style>
        body { font-family: Arial; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; margin: 20px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        .success { color: #10b981; background: #d1fae5; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { color: #ef4444; background: #fee2e2; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { color: #3b82f6; background: #dbeafe; padding: 15px; border-radius: 8px; margin: 10px 0; }
        code { background: #1e293b; color: #e2e8f0; padding: 3px 8px; border-radius: 5px; }
        pre { background: #1e293b; color: #e2e8f0; padding: 20px; border-radius: 8px; overflow-x: auto; }
        .btn { background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; margin: 10px 5px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: bold; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔍 Path Configuration Test</h1>
        <p>Testing if paths are correctly configured for your live site.</p>
        
        <h2>PHP Configuration</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
            </tr>
            <tr>
                <td><strong>BASE_PATH</strong></td>
                <td><code><?php echo BASE_PATH === '' ? '(empty - site at root)' : BASE_PATH; ?></code></td>
            </tr>
            <tr>
                <td><strong>ASSET_VERSION</strong></td>
                <td><code><?php echo ASSET_VERSION; ?></code></td>
            </tr>
            <tr>
                <td><strong>Environment</strong></td>
                <td><code><?php echo isLocalEnvironment() ? 'LOCAL' : 'LIVE'; ?></code></td>
            </tr>
            <tr>
                <td><strong>Database</strong></td>
                <td><code><?php echo DB_NAME; ?></code></td>
            </tr>
        </table>
        
        <h2>Generated Paths (What HTML will output)</h2>
        <div class="info">
            <strong>CSS Path:</strong><br>
            <code>&lt;link href="<?php echo BASE_PATH; ?>/style.css"&gt;</code><br>
            Resolves to: <code><?php echo BASE_PATH; ?>/style.css</code>
        </div>
        
        <div class="info">
            <strong>JS Path:</strong><br>
            <code>&lt;script src="<?php echo BASE_PATH; ?>/auth.js"&gt;</code><br>
            Resolves to: <code><?php echo BASE_PATH; ?>/auth.js</code>
        </div>
        
        <div class="info">
            <strong>API Path:</strong><br>
            <code>fetch('<?php echo BASE_PATH; ?>/auth-api.php')</code><br>
            Resolves to: <code><?php echo BASE_PATH; ?>/auth-api.php</code>
        </div>
        
        <h2>JavaScript Test</h2>
        <button onclick="testJavaScript()" class="btn">Test JavaScript Paths</button>
        <div id="jsResult"></div>
        
        <h2>File Existence Check</h2>
        <button onclick="testFiles()" class="btn">Check if Files Exist</button>
        <div id="fileResult"></div>
    </div>
    
    <script>
        // Pass BASE_PATH to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
        
        function testJavaScript() {
            const resultDiv = document.getElementById('jsResult');
            const apiUrl = ((typeof BASE_PATH !== 'undefined' && BASE_PATH) ? BASE_PATH : '') + '/auth-api.php';
            
            resultDiv.innerHTML = `
                <div class="success">
                    <strong>✓ JavaScript is working!</strong><br><br>
                    <strong>BASE_PATH value:</strong> <code>${BASE_PATH === '' ? '(empty string)' : BASE_PATH}</code><br>
                    <strong>Constructed API URL:</strong> <code>${apiUrl}</code>
                </div>
            `;
        }
        
        async function testFiles() {
            const resultDiv = document.getElementById('fileResult');
            resultDiv.innerHTML = '<p>Testing file access...</p>';
            
            const files = [
                BASE_PATH + '/style.css',
                BASE_PATH + '/auth.js',
                BASE_PATH + '/dashboard.js',
                BASE_PATH + '/auth-api.php',
                BASE_PATH + '/api.php'
            ];
            
            let html = '<table><tr><th>File</th><th>Status</th></tr>';
            
            for (const file of files) {
                try {
                    const response = await fetch(file, { method: 'HEAD' });
                    const status = response.ok ? 
                        '<span style="color: #10b981;">✓ Accessible (' + response.status + ')</span>' : 
                        '<span style="color: #ef4444;">✗ Error (' + response.status + ')</span>';
                    html += `<tr><td><code>${file}</code></td><td>${status}</td></tr>`;
                } catch (error) {
                    html += `<tr><td><code>${file}</code></td><td><span style="color: #ef4444;">✗ Failed: ${error.message}</span></td></tr>`;
                }
            }
            
            html += '</table>';
            resultDiv.innerHTML = html;
        }
    </script>
    
    <div class="box">
        <h2>✅ If Everything Looks Good</h2>
        <div class="success">
            <p>If the paths above look correct, your site should work properly!</p>
            <a href="login" class="btn">Go to Login Page</a>
        </div>
        
        <h3>Expected Results:</h3>
        <ul>
            <li>BASE_PATH should be empty or show your subdirectory</li>
            <li>All paths should start with <code>/</code></li>
            <li>No double slashes like <code>//</code></li>
            <li>File existence check should show all files as accessible</li>
        </ul>
    </div>
</body>
</html>

