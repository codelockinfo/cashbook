// PWA Functionality - Install Prompt and Service Worker Registration
let deferredPrompt;
let installButton;

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const swPath = BASE_PATH ? `${BASE_PATH}/service-worker.js.php` : '/service-worker.js.php';
        const swScope = BASE_PATH ? `${BASE_PATH}/` : '/';
        
        navigator.serviceWorker.register(swPath, {
            scope: swScope
        })
        .then((registration) => {
            // Check for updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        showUpdateNotification();
                    }
                });
            });
        })
        .catch((error) => {
            // Service Worker registration failed
        });
    });
}

// Capture the install prompt event
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent the default mini-infobar
    e.preventDefault();
    
    // Store the event for later use
    deferredPrompt = e;
    
    // Don't show install button in header - only use floating button
    // showInstallButton(); // DISABLED
    
    // Update floating button visibility
    updateFloatingButton();
});

// Show install button
function showInstallButton() {
    // Create install button if it doesn't exist
    if (!document.getElementById('pwaInstallBtn')) {
        const installBtn = document.createElement('button');
        installBtn.id = 'pwaInstallBtn';
        installBtn.className = 'pwa-install-btn';
        installBtn.innerHTML = '<i class="fas fa-download"></i> Install App';
        installBtn.addEventListener('click', installPWA);
        
        // Add to body or header
        const header = document.querySelector('.header');
        if (header) {
            const headerActions = header.querySelector('.header-actions');
            if (headerActions) {
                headerActions.insertBefore(installBtn, headerActions.firstChild);
            }
        }
    }
}

// Install PWA - Make it globally accessible
window.installPWA = async function installPWA() {
    if (!deferredPrompt) {
        // If iOS, show instructions
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            alert('To install on iOS:\n\n1. Tap the Share button (⎙)\n2. Select "Add to Home Screen"\n3. Tap "Add"');
        } else {
            alert('PWA installation not available.\n\nYour app may already be installed, or your browser doesn\'t support it.');
        }
        return;
    }
    
    // Hide the banner first
    hideInstallPopup();
    
    // Show the install prompt
    deferredPrompt.prompt();
    
    // Wait for user response
    const { outcome } = await deferredPrompt.userChoice;
    
    if (outcome === 'accepted') {
        // Mark as installed in localStorage
        localStorage.setItem('pwa-installed', 'true');
        
        if (typeof showToast === 'function') {
            showToast('App installed successfully! Check your home screen.', 'success');
        }
        
        // Hide floating button permanently
        const floatingBtn = document.getElementById('floatingInstallBtn');
        if (floatingBtn) {
            floatingBtn.style.setProperty('display', 'none', 'important');
        }
    }
    
    // Clear the prompt
    deferredPrompt = null;
};

// Handle app installed event
window.addEventListener('appinstalled', (e) => {
    // Mark as installed in localStorage
    localStorage.setItem('pwa-installed', 'true');
    
    if (typeof showToast === 'function') {
        showToast('Cash Book installed! Launch from your home screen.', 'success');
    }
    
    // Hide install button
    const installBtn = document.getElementById('pwaInstallBtn');
    if (installBtn) {
        installBtn.style.display = 'none';
    }
    
    // Hide floating button permanently
    const floatingBtn = document.getElementById('floatingInstallBtn');
    if (floatingBtn) {
        floatingBtn.style.setProperty('display', 'none', 'important');
    }
    
    deferredPrompt = null;
});

// Check if running as installed PWA
function isRunningAsPWA() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true;
}

// Show different UI if running as PWA
if (isRunningAsPWA()) {
    document.body.classList.add('pwa-mode');
}

// Handle online/offline status
window.addEventListener('online', () => {
    if (typeof showToast === 'function') {
        showToast('Connection restored', 'success');
    }
});

window.addEventListener('offline', () => {
    if (typeof showToast === 'function') {
        showToast('You are offline. Some features may not work.', 'error');
    }
});

// Show update notification - DISABLED
function showUpdateNotification() {
    // Update notification functionality disabled
    // The app will use the latest cached version without showing update prompts
    
    /* DISABLED - Uncomment if you want to re-enable update notifications
    if (typeof showToast === 'function') {
        showToast('New version available! Refresh to update.', 'success');
    }
    
    // Create update button
    const updateBtn = document.createElement('button');
    updateBtn.className = 'pwa-update-btn';
    updateBtn.innerHTML = '<i class="fas fa-sync"></i> Update Available';
    updateBtn.addEventListener('click', () => {
        window.location.reload();
    });
    
    document.body.appendChild(updateBtn);
    */
}

// Show install banner at bottom
function showInstallBanner(delay = 2000) {
    const banner = document.getElementById('pwaInstallBanner');
    
    if (banner && !isRunningAsPWA()) {
        // Show banner after specified delay - CSS handles the animation
        setTimeout(() => {
            banner.classList.add('show');
        }, delay);
    }
}

// Check if banner was dismissed
function isInstallBannerDismissed() {
    return localStorage.getItem('pwa-banner-dismissed') === 'true';
}

// Dismiss install banner
function dismissInstallBanner() {
    const banner = document.getElementById('pwaInstallBanner');
    
    if (banner) {
        banner.classList.remove('show');
    }
    
    // Remember dismissal (but will be cleared on next login)
    localStorage.setItem('pwa-banner-dismissed', 'true');
}

// Setup banner event listeners
document.addEventListener('DOMContentLoaded', () => {
    const closeBannerBtn = document.getElementById('closePWABanner');
    const installBannerBtn = document.getElementById('installPWABanner');
    
    if (closeBannerBtn) {
        closeBannerBtn.addEventListener('click', dismissInstallBanner);
    }
    
    if (installBannerBtn) {
        installBannerBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                // Show install prompt
                deferredPrompt.prompt();
                
                const { outcome } = await deferredPrompt.userChoice;
                
                if (outcome === 'accepted') {
                    // Mark as installed
                    localStorage.setItem('pwa-installed', 'true');
                    
                    // Hide floating button
                    const floatingBtn = document.getElementById('floatingInstallBtn');
                    if (floatingBtn) {
                        floatingBtn.style.setProperty('display', 'none', 'important');
                    }
                    
                    dismissInstallBanner();
                    
                    if (typeof showToast === 'function') {
                        showToast('App installed successfully!', 'success');
                    }
                }
                
                deferredPrompt = null;
            } else {
                // Check if already installed
                const isInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
                
                if (isInstalled) {
                    alert('App is already installed! Launch it from your home screen.');
                    dismissInstallBanner();
                } else if (isIOS) {
                    // Show iOS instructions
                    alert('To install on iOS:\n\n1. Tap the Share button (⎙) at the bottom\n2. Scroll down and tap "Add to Home Screen"\n3. Tap "Add" to install');
                } else {
                    alert('Installation not available.\n\nThis may happen if:\n- App is already installed\n- Browser doesn\'t support PWA\n- You need to access via HTTPS');
                }
            }
        });
    }
});

// iOS PWA detection and guidance
function checkIOSInstall() {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isInStandaloneMode = window.navigator.standalone;
    
    if (isIOS && !isInStandaloneMode) {
        // Show iOS install instructions in banner
        const currentPage = window.location.pathname.split('/').pop();
        if ((currentPage === 'dashboard.php' || currentPage === 'dashboard' || currentPage === 'groups.php' || currentPage === 'groups') && !isInstallBannerDismissed()) {
            showIOSInstallBanner(500); // Show immediately on iOS mobile
        }
    }
}

// Show iOS-specific install banner
function showIOSInstallBanner(delay = 500) {
    const banner = document.getElementById('pwaInstallBanner');
    
    if (banner) {
        // Update banner for iOS
        const bannerText = banner.querySelector('.pwa-banner-text');
        if (bannerText) {
            bannerText.innerHTML = `
                <h4>📱 Install Cash Book App</h4>
                <p>Tap <i class="fas fa-share"></i> Share button, then select "Add to Home Screen"</p>
            `;
        }
        
        // Hide install button for iOS (manual install only)
        const installBtn = document.getElementById('installPWABanner');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
        
        // Show banner immediately on mobile - CSS handles the animation
        setTimeout(() => {
            banner.classList.add('show');
        }, delay);
    }
}

// Call on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkIOSInstall);
} else {
    checkIOSInstall();
}

// Check if just logged in and on mobile - show install prompt immediately
function checkFirstVisitInstall() {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const currentPage = window.location.pathname.split('/').pop();
    const isJustLoggedIn = sessionStorage.getItem('just_logged_in') === 'true';
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAlreadyInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    
    // If on mobile, just logged in, and on dashboard, and NOT already installed
    if (isMobile && isJustLoggedIn && !isAlreadyInstalled && (currentPage === 'dashboard' || currentPage === 'dashboard.php')) {
        // Clear the login flag
        sessionStorage.removeItem('just_logged_in');
        
        // IMPORTANT: Clear the dismissed flag on login - always show after fresh login!
        localStorage.removeItem('pwa-banner-dismissed');
        
        // For iOS - show instructions immediately (iOS doesn't support beforeinstallprompt)
        if (isIOS) {
            showIOSInstallBanner(800); // Show after 800ms
            return;
        }
        
        // For Android/other mobile - ALWAYS show banner after login
        showInstallBanner(800); // Show after 800ms
    }
}

// Run check on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkFirstVisitInstall);
} else {
    setTimeout(checkFirstVisitInstall, 100);
}

// Show install popup (deprecated - now calls installPWA directly)
window.showInstallPopup = function() {
    // Call installPWA directly (no popup banner)
    if (typeof window.installPWA === 'function') {
        window.installPWA();
    } else {
        alert('Install feature not available at this time.');
    }
};

// Hide install popup (deprecated - no longer used)
window.hideInstallPopup = function() {
    // No-op function for backward compatibility
};

// Show/hide floating button based on conditions
function updateFloatingButton() {
    const floatingBtn = document.getElementById('floatingInstallBtn');
    
    if (!floatingBtn) return;
    
    // Check if user already installed the app
    const wasInstalled = localStorage.getItem('pwa-installed') === 'true';
    if (wasInstalled) {
        return;
    }
    
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isAlreadyInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    
    // If app is running in standalone mode, mark as installed and hide button
    if (isAlreadyInstalled) {
        localStorage.setItem('pwa-installed', 'true');
        floatingBtn.style.setProperty('display', 'none', 'important');
        return;
    }
    
    // Show button if not installed (on any device)
    if (!isAlreadyInstalled) {
        floatingBtn.style.display = 'flex';
    } else {
        floatingBtn.style.display = 'none';
    }
}

// Run on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Remove any existing top install buttons
        const topInstallBtn = document.getElementById('pwaInstallBtn');
        if (topInstallBtn) {
            topInstallBtn.remove();
        }
        
        updateFloatingButton();
    });
} else {
    // Remove any existing top install buttons
    const topInstallBtn = document.getElementById('pwaInstallBtn');
    if (topInstallBtn) {
        topInstallBtn.remove();
    }
    
    updateFloatingButton();
}

// Also add click listener to floating button as backup
document.addEventListener('DOMContentLoaded', () => {
    const floatingBtn = document.getElementById('floatingInstallBtn');
    
    if (floatingBtn) {
        // Add click listener as backup to onclick attribute
        floatingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Call installPWA directly (no popup, just install)
            if (typeof window.installPWA === 'function') {
                window.installPWA();
            } else {
                alert('Install not available at this time.');
            }
        });
    }
});

