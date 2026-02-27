<?php
require_once 'config.php';

// Set correct content type for JavaScript
header('Content-Type: application/javascript');
header('Service-Worker-Allowed: /');

// Get base path
$basePath = BASE_PATH;

// Output JavaScript service worker
?>
// Service Worker for Cash Book PWA
// Auto-generated with correct paths for this environment

const CACHE_NAME = 'cashbook-v1.4.0';
const RUNTIME_CACHE = 'cashbook-runtime-v1.4.0';
const BASE_PATH = '<?php echo $basePath; ?>';

// Files to cache immediately on install
const STATIC_CACHE_URLS = [
    BASE_PATH + '/',
    BASE_PATH + '/login',
    BASE_PATH + '/dashboard',
    BASE_PATH + '/groups',
    BASE_PATH + '/profile',
    BASE_PATH + '/style16.css',
    BASE_PATH + '/auth-style9.css',
    BASE_PATH + '/dashboard9.js',
    BASE_PATH + '/auth9.js',
    BASE_PATH + '/groups17.js',
    BASE_PATH + '/pwa10.js',
    BASE_PATH + '/icons/icon-192x192.png',
    BASE_PATH + '/icons/icon-512x512.png',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
].map(url => url.replace(/\/+/g, '/')); // Clean up double slashes

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => {
                console.log('[Service Worker] Installation complete');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[Service Worker] Installation failed:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter((cacheName) => {
                            return cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE;
                        })
                        .map((cacheName) => {
                            console.log('[Service Worker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(() => {
                console.log('[Service Worker] Activation complete');
                return self.clients.claim();
            })
    );
});

// Fetch event - network first, then cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }
    
    // Skip API requests from being cached (always fetch fresh data)
    if (request.url.includes('api.php') || 
        request.url.includes('auth-api.php') || 
        request.url.includes('profile-api.php') ||
        request.url.includes('group-api.php')) {
        event.respondWith(fetch(request));
        return;
    }
    
    // For other requests, try network first, fallback to cache
    event.respondWith(
        fetch(request)
            .then((response) => {
                // Clone the response
                const responseClone = response.clone();
                
                // Cache the response for future use
                caches.open(RUNTIME_CACHE)
                    .then((cache) => {
                        cache.put(request, responseClone);
                    });
                
                return response;
            })
            .catch(() => {
                // If network fails, try cache
                return caches.match(request)
                    .then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        
                        // If not in cache either, return offline page
                        if (request.mode === 'navigate') {
                            return caches.match(BASE_PATH + '/offline.html');
                        }
                    });
            })
    );
});

// Handle background sync (optional - for future use)
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);
    
    if (event.tag === 'sync-transactions') {
        event.waitUntil(syncTransactions());
    }
});

// Sync pending transactions when back online
async function syncTransactions() {
    console.log('[Service Worker] Syncing transactions...');
    // Implementation for syncing offline transactions
    // This can be implemented later if needed
}

// Push notification support (optional - for future use)
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push received');
    
    const options = {
        body: event.data ? event.data.text() : 'New notification from Cash Book',
        icon: BASE_PATH + '/icons/icon-192x192.png',
        badge: BASE_PATH + '/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        }
    };
    
    event.waitUntil(
        self.registration.showNotification('Cash Book', options)
    );
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    event.notification.close();
    
    event.waitUntil(
        clients.openWindow(BASE_PATH + '/dashboard')
    );
});

