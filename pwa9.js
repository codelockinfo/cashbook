// PWA Functionality - Install Prompt and Service Worker Registration
console.log('🚀 pwa9.js LOADED at:', new Date().toLocaleTimeString());
console.log('🔍 Initial check:', {
    url: window.location.href,
    pathname: window.location.pathname,
    isSecure: window.location.protocol === 'https:' || window.location.hostname === 'localhost',
    userAgent: navigator.userAgent.substring(0, 50) + '...'
});

let deferredPrompt;
let installButton;

// Use BASE_PATH from window (already defined in dashboard.php)
// Don't redeclare it here to avoid "Identifier already declared" error
console.log('📂 Using global BASE_PATH:', window.BASE_PATH || '');

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
    console.log('💾 beforeinstallprompt event fired! Install prompt is now available');
    console.log('📦 Event object:', e);
    
    // Prevent the default mini-infobar
    e.preventDefault();
    
    // Store the event for later use
    deferredPrompt = e;
    console.log('✅ deferredPrompt stored:', !!deferredPrompt);
    
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
    console.log('🎯 installPWA() function called!');
    
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
        
        // Mark as installed in localStorage
        localStorage.setItem('pwa-installed', 'true');
        console.log('💾 Saved installation state to localStorage');
        
        if (typeof showToast === 'function') {
            showToast('App installed successfully! Check your home screen.', 'success');
        }
        
        // Hide floating button permanently
        const floatingBtn = document.getElementById('floatingInstallBtn');
        if (floatingBtn) {
            floatingBtn.style.setProperty('display', 'none', 'important');
            console.log('✅ Floating button hidden permanently');
        }
    } else {
        console.log('❌ PWA installation declined');
    }
    
    // Clear the prompt
    deferredPrompt = null;
};

console.log('✅ window.installPWA function defined:', typeof window.installPWA);

// Handle app installed event
window.addEventListener('appinstalled', (e) => {
    console.log('✅ PWA installed successfully');
    
    // Mark as installed in localStorage
    localStorage.setItem('pwa-installed', 'true');
    console.log('💾 Saved installation state to localStorage (appinstalled event)');
    
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
        console.log('✅ Floating button hidden permanently (appinstalled event)');
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
    console.log('📋 DOMContentLoaded fired - setting up banner listeners');
    const closeBannerBtn = document.getElementById('closePWABanner');
    const installBannerBtn = document.getElementById('installPWABanner');
    
    console.log('🔍 Banner elements found:', {
        closeBannerBtn: !!closeBannerBtn,
        installBannerBtn: !!installBannerBtn
    });
    
    if (closeBannerBtn) {
        closeBannerBtn.addEventListener('click', dismissInstallBanner);
        console.log('✅ Close button listener attached');
    }
    
    if (installBannerBtn) {
        installBannerBtn.addEventListener('click', async () => {
            console.log('🖱️ Install button clicked!');
            console.log('🔍 deferredPrompt status:', deferredPrompt ? 'Available' : 'NULL');
            
            if (deferredPrompt) {
                console.log('✅ Showing install prompt...');
                
                // Show install prompt
                deferredPrompt.prompt();
                
                const { outcome } = await deferredPrompt.userChoice;
                
                console.log('📊 User choice:', outcome);
                
                if (outcome === 'accepted') {
                    console.log('✅ PWA installed from banner');
                    
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
                } else {
                    console.log('❌ PWA installation declined from banner');
                }
                
                deferredPrompt = null;
            } else {
                console.error('❌ deferredPrompt is null!');
                console.log('ℹ️ This means the beforeinstallprompt event did not fire.');
                
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
        console.log('✅ Install button listener attached');
    } else {
        console.error('❌ Install button not found!');
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

// Show install popup (deprecated - now calls installPWA directly)
window.showInstallPopup = function() {
    console.log('🎯 showInstallPopup called - redirecting to installPWA()');
    
    // Call installPWA directly (no popup banner)
    if (typeof window.installPWA === 'function') {
        window.installPWA();
    } else {
        console.error('❌ installPWA function not available');
        alert('Install feature not available at this time.');
    }
};

// Hide install popup (deprecated - no longer used)
window.hideInstallPopup = function() {
    console.log('ℹ️ hideInstallPopup called but popup is removed');
};

// Show/hide floating button based on conditions
function updateFloatingButton() {
    const floatingBtn = document.getElementById('floatingInstallBtn');
    
    if (!floatingBtn) return;
    
    // Check if user already installed the app
    const wasInstalled = localStorage.getItem('pwa-installed') === 'true';
    if (wasInstalled) {
        console.log('✅ App already installed - hiding button permanently');
        // floatingBtn.style.setProperty('display', 'none', 'important');
        return;
    }
    
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const isAlreadyInstalled = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    
    console.log('🔍 Floating button check:', {
        isMobile,
        isAlreadyInstalled,
        wasInstalled,
        buttonExists: !!floatingBtn
    });
    
    // If app is running in standalone mode, mark as installed and hide button
    if (isAlreadyInstalled) {
        localStorage.setItem('pwa-installed', 'true');
        floatingBtn.style.setProperty('display', 'none', 'important');
        console.log('✅ App running in standalone mode - marked as installed');
        return;
    }
    
    // Show button if not installed (on any device)
    if (!isAlreadyInstalled) {
        floatingBtn.style.display = 'flex';
        console.log('✅ Showing floating install button');
    } else {
        floatingBtn.style.display = 'none';
        console.log('ℹ️ Hiding floating install button (not mobile or already installed)');
    }
}

// Run on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Remove any existing top install buttons
        const topInstallBtn = document.getElementById('pwaInstallBtn');
        if (topInstallBtn) {
            topInstallBtn.remove();
            console.log('🗑️ Removed top install button');
        }
        
        updateFloatingButton();
    });
} else {
    // Remove any existing top install buttons
    const topInstallBtn = document.getElementById('pwaInstallBtn');
    if (topInstallBtn) {
        topInstallBtn.remove();
        console.log('🗑️ Removed top install button');
    }
    
    updateFloatingButton();
}

// Also add click listener to floating button as backup
document.addEventListener('DOMContentLoaded', () => {
    const floatingBtn = document.getElementById('floatingInstallBtn');
    
    if (floatingBtn) {
        console.log('✅ Found floating button, adding click listener');
        
        // Add click listener as backup to onclick attribute
        floatingBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('🖱️ Floating button CLICKED via addEventListener!');
            
            // Call installPWA directly (no popup, just install)
            if (typeof window.installPWA === 'function') {
                console.log('✅ Calling window.installPWA()');
                window.installPWA();
            } else {
                console.error('❌ window.installPWA is not a function!');
                alert('Install not available at this time.');
            }
        });
        
        console.log('✅ Click listener added to floating button');
    } else {
        console.log('❌ Floating button not found in DOM');
    }
});

