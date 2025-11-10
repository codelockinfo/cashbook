// PWA Functionality - Install Prompt and Service Worker Registration

let deferredPrompt;
let installButton;

// Get base path from window or default to current directory
const BASE_PATH = window.BASE_PATH || '';

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        const swPath = BASE_PATH ? `${BASE_PATH}/service-worker.js.php` : '/service-worker.js.php';
        const swScope = BASE_PATH ? `${BASE_PATH}/` : '/';
        
        navigator.serviceWorker.register(swPath, {
            scope: swScope
        })
        .then((registration) => {
            console.log('✅ Service Worker registered successfully:', registration.scope);
            
            // Check for updates
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                console.log('🔄 New Service Worker found, installing...');
                
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        console.log('✨ New content available, please refresh');
                        showUpdateNotification();
                    }
                });
            });
        })
        .catch((error) => {
            console.error('❌ Service Worker registration failed:', error);
        });
    });
}

// Capture the install prompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('💾 Install prompt available');
    
    // Prevent the default mini-infobar
    e.preventDefault();
    
    // Store the event for later use
    deferredPrompt = e;
    
    // Show custom install button in header
    showInstallButton();
    
    // Show bottom banner if not dismissed and on dashboard/groups page
    const currentPage = window.location.pathname.split('/').pop();
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    if ((currentPage === 'dashboard.php' || currentPage === 'dashboard' || currentPage === 'groups.php' || currentPage === 'groups') && !isInstallBannerDismissed()) {
        // On mobile, show immediately; on desktop, show after delay
        if (isMobile) {
            showInstallBanner(500); // Show after 500ms on mobile
        } else {
            showInstallBanner(2000); // Show after 2 seconds on desktop
        }
    }
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

// Install PWA
async function installPWA() {
    if (!deferredPrompt) {
        console.log('Install prompt not available');
        return;
    }
    
    // Show the install prompt
    deferredPrompt.prompt();
    
    // Wait for user response
    const { outcome } = await deferredPrompt.userChoice;
    
    console.log(`User response to install prompt: ${outcome}`);
    
    if (outcome === 'accepted') {
        console.log('✅ PWA installation accepted');
        showToast('App installed successfully! Check your home screen.', 'success');
    } else {
        console.log('❌ PWA installation declined');
    }
    
    // Clear the prompt
    deferredPrompt = null;
    
    // Hide install button
    const installBtn = document.getElementById('pwaInstallBtn');
    if (installBtn) {
        installBtn.style.display = 'none';
    }
}

// Handle app installed event
window.addEventListener('appinstalled', (e) => {
    console.log('✅ PWA installed successfully');
    showToast('Cash Book installed! Launch from your home screen.', 'success');
    
    // Hide install button
    const installBtn = document.getElementById('pwaInstallBtn');
    if (installBtn) {
        installBtn.style.display = 'none';
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
    console.log('🚀 Running as installed PWA');
    document.body.classList.add('pwa-mode');
}

// Handle online/offline status
window.addEventListener('online', () => {
    console.log('🌐 Back online');
    if (typeof showToast === 'function') {
        showToast('Connection restored', 'success');
    }
});

window.addEventListener('offline', () => {
    console.log('📵 Gone offline');
    if (typeof showToast === 'function') {
        showToast('You are offline. Some features may not work.', 'error');
    }
});

// Show update notification
function showUpdateNotification() {
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
}

// Show install banner at bottom
function showInstallBanner(delay = 2000) {
    const banner = document.getElementById('pwaInstallBanner');
    
    if (banner && !isRunningAsPWA()) {
        // Show banner after specified delay
        setTimeout(() => {
            banner.style.display = 'block';
            setTimeout(() => {
                banner.classList.add('show');
            }, 100);
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
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);
    }
    
    // Remember dismissal
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
                    console.log('✅ PWA installed from banner');
                    dismissInstallBanner();
                }
                
                deferredPrompt = null;
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
                <h4>Install Cash Book App</h4>
                <p>Tap <i class="fas fa-share"></i> Share, then "Add to Home Screen"</p>
            `;
        }
        
        // Hide install button for iOS (manual install only)
        const installBtn = document.getElementById('installPWABanner');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
        
        // Show banner immediately on mobile
        setTimeout(() => {
            banner.style.display = 'block';
            setTimeout(() => {
                banner.classList.add('show');
            }, 100);
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
    
    console.log('🔍 PWA Install Check:', {
        isMobile,
        currentPage,
        isJustLoggedIn,
        isIOS,
        hasDeferredPrompt: !!deferredPrompt,
        bannerDismissed: isInstallBannerDismissed()
    });
    
    // If on mobile, just logged in, and on dashboard
    if (isMobile && isJustLoggedIn && (currentPage === 'dashboard' || currentPage === 'dashboard.php')) {
        console.log('📱 ✅ First visit after login on mobile - WILL SHOW INSTALL PROMPT');
        
        // Clear the flag
        sessionStorage.removeItem('just_logged_in');
        
        // For iOS - show instructions immediately (iOS doesn't support beforeinstallprompt)
        if (isIOS) {
            console.log('📱 iOS detected - showing install instructions banner');
            if (!isInstallBannerDismissed()) {
                showIOSInstallBanner(500);
            } else {
                console.log('⚠️ Banner was previously dismissed by user');
            }
            return;
        }
        
        // For Android/other mobile - wait for deferredPrompt or show anyway
        if (!isInstallBannerDismissed()) {
            // Try to show banner immediately if deferred prompt is ready
            if (deferredPrompt) {
                console.log('✅ Deferred prompt ready - showing banner NOW');
                showInstallBanner(500);
            } else {
                console.log('⏳ Deferred prompt not ready yet - waiting for it...');
                // Wait up to 5 seconds for beforeinstallprompt event
                let attempts = 0;
                const maxAttempts = 50; // 5 seconds (50 x 100ms)
                const checkInterval = setInterval(() => {
                    attempts++;
                    console.log(`⏳ Waiting for install prompt... (${attempts}/${maxAttempts})`);
                    
                    if (deferredPrompt) {
                        console.log('✅ Deferred prompt is NOW ready - SHOWING BANNER!');
                        clearInterval(checkInterval);
                        showInstallBanner(100);
                    } else if (attempts >= maxAttempts) {
                        console.log('⚠️ Deferred prompt not available after 5 seconds');
                        console.log('   Possible reasons: Already installed, not HTTPS, or browser doesn\'t support PWA');
                        clearInterval(checkInterval);
                        
                        // Show banner anyway for visual feedback
                        showInstallBanner(100);
                    }
                }, 100);
            }
        } else {
            console.log('ℹ️ Install banner was previously dismissed by user');
        }
    } else {
        if (!isMobile) console.log('ℹ️ Not on mobile device - banner won\'t auto-show');
        if (!isJustLoggedIn) console.log('ℹ️ Not just logged in - banner won\'t auto-show');
        if (currentPage !== 'dashboard' && currentPage !== 'dashboard.php') console.log('ℹ️ Not on dashboard page');
    }
}

// Run check on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkFirstVisitInstall);
} else {
    setTimeout(checkFirstVisitInstall, 100);
}

