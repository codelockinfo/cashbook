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
    console.log('🎯 Install button clicked!');
    
    if (!deferredPrompt) {
        console.log('⚠️ Install prompt not available - may be already installed or not supported');
        
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
    
    console.log(`User response to install prompt: ${outcome}`);
    
    if (outcome === 'accepted') {
        console.log('✅ PWA installation accepted');
        if (typeof showToast === 'function') {
            showToast('App installed successfully! Check your home screen.', 'success');
        }
        
        // Hide floating button
        const floatingBtn = document.getElementById('floatingInstallBtn');
        if (floatingBtn) {
            floatingBtn.style.display = 'none';
        }
    } else {
        console.log('❌ PWA installation declined');
    }
    
    // Clear the prompt
    deferredPrompt = null;
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
    
    console.log('🎯 showInstallBanner called:', {
        bannerExists: !!banner,
        isRunningAsPWA: isRunningAsPWA(),
        delay: delay
    });
    
    if (banner && !isRunningAsPWA()) {
        console.log('✅ Banner element found - WILL SHOW in ' + delay + 'ms');
        // Show banner after specified delay - CSS handles the animation
        setTimeout(() => {
            console.log('📢 SHOWING BANNER NOW - Adding .show class!');
            banner.classList.add('show');
            console.log('✨ .show class added - banner should slide up from bottom!');
        }, delay);
    } else {
        if (!banner) console.log('❌ Banner element NOT found in DOM!');
        if (isRunningAsPWA()) console.log('ℹ️ Already running as PWA - no need to show banner');
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
        console.log('❌ User dismissed install banner');
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
    
    console.log('🍎 showIOSInstallBanner called:', {
        bannerExists: !!banner,
        delay: delay
    });
    
    if (banner) {
        console.log('✅ Banner found - preparing iOS instructions');
        // Update banner for iOS
        const bannerText = banner.querySelector('.pwa-banner-text');
        if (bannerText) {
            bannerText.innerHTML = `
                <h4>📱 Install Cash Book App</h4>
                <p>Tap <i class="fas fa-share"></i> Share button, then select "Add to Home Screen"</p>
            `;
            console.log('✅ Updated banner text for iOS');
        }
        
        // Hide install button for iOS (manual install only)
        const installBtn = document.getElementById('installPWABanner');
        if (installBtn) {
            installBtn.style.display = 'none';
            console.log('ℹ️ Hidden install button (iOS uses manual install)');
        }
        
        // Show banner immediately on mobile - CSS handles the animation
        setTimeout(() => {
            console.log('📢 SHOWING iOS BANNER NOW - Adding .show class!');
            banner.classList.add('show');
            console.log('✨ .show class added - iOS banner should slide up from bottom!');
        }, delay);
    } else {
        console.log('❌ Banner element NOT found in DOM!');
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
    
    console.log('🔍 PWA Install Check:', {
        isMobile,
        currentPage,
        isJustLoggedIn,
        isIOS,
        isAlreadyInstalled,
        hasDeferredPrompt: !!deferredPrompt,
        bannerDismissed: isInstallBannerDismissed()
    });
    
    // If on mobile, just logged in, and on dashboard, and NOT already installed
    if (isMobile && isJustLoggedIn && !isAlreadyInstalled && (currentPage === 'dashboard' || currentPage === 'dashboard.php')) {
        console.log('📱 ✅ First visit after login on mobile - WILL SHOW INSTALL BANNER!');
        
        // Clear the login flag
        sessionStorage.removeItem('just_logged_in');
        
        // IMPORTANT: Clear the dismissed flag on login - always show after fresh login!
        console.log('🔓 Clearing any previous dismissal - banner WILL show after login!');
        localStorage.removeItem('pwa-banner-dismissed');
        
        // For iOS - show instructions immediately (iOS doesn't support beforeinstallprompt)
        if (isIOS) {
            console.log('📱 iOS detected - SHOWING install instructions banner NOW!');
            showIOSInstallBanner(800); // Show after 800ms
            return;
        }
        
        // For Android/other mobile - ALWAYS show banner after login
        console.log('📱 Android/Mobile detected - SHOWING install banner NOW!');
        showInstallBanner(800); // Show after 800ms
        
    } else {
        if (!isMobile) console.log('ℹ️ Not on mobile device');
        if (!isJustLoggedIn) console.log('ℹ️ Not just logged in');
        if (isAlreadyInstalled) console.log('ℹ️ App is already installed');
        if (currentPage !== 'dashboard' && currentPage !== 'dashboard.php') console.log('ℹ️ Not on dashboard page');
    }
}

// Run check on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkFirstVisitInstall);
} else {
    setTimeout(checkFirstVisitInstall, 100);
}

// Show install popup when floating button is clicked
window.showInstallPopup = function() {
    console.log('🎯 showInstallPopup called!');
    
    const banner = document.getElementById('pwaInstallBanner');
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    
    console.log('📱 Floating install button clicked!', {
        bannerExists: !!banner,
        isIOS: isIOS
    });
    
    if (!banner) {
        console.log('❌ Banner element not found!');
        alert('Install banner not found. Please refresh the page.');
        return;
    }
    
    // For iOS - update banner with iOS instructions
    if (isIOS) {
        console.log('📱 iOS device - showing iOS install instructions');
        const bannerText = banner.querySelector('.pwa-banner-text');
        if (bannerText) {
            bannerText.innerHTML = `
                <h4>📱 Install Cash Book App</h4>
                <p>1. Tap the <i class="fas fa-share"></i> Share button below<br>
                2. Select "Add to Home Screen"<br>
                3. Tap "Add" to install</p>
            `;
        }
        
        // Hide install button for iOS (manual install only)
        const installBtn = document.getElementById('installPWABanner');
        if (installBtn) {
            installBtn.style.display = 'none';
        }
    }
    
    // Show the banner
    console.log('📢 Showing install popup NOW!');
    banner.classList.add('show');
    
    console.log('Banner classes after adding show:', banner.className);
    console.log('Banner computed transform:', window.getComputedStyle(banner).transform);
};

// Hide install popup
window.hideInstallPopup = function() {
    const banner = document.getElementById('pwaInstallBanner');
    
    if (banner) {
        console.log('🔽 Hiding install popup');
        banner.classList.remove('show');
    }
};

// Show/hide floating button based on conditions
function updateFloatingButton() {
    const floatingBtn = document.getElementById('floatingInstallBtn');
    
    if (!floatingBtn) return;
    
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isAlreadyInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    
    console.log('🔍 Floating button check:', {
        isMobile,
        isAlreadyInstalled,
        buttonExists: !!floatingBtn
    });
    
    // Show button on mobile if not installed
    if (isMobile && !isAlreadyInstalled) {
        floatingBtn.style.display = 'flex';
        console.log('✅ Showing floating install button');
    } else {
        floatingBtn.style.display = 'none';
        console.log('ℹ️ Hiding floating install button');
    }
}

// Run on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateFloatingButton);
} else {
    updateFloatingButton();
}

