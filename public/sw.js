/**
 * LULU Couture — Service Worker
 * Handles: Offline caching, push notifications, background sync
 */

const CACHE_NAME = 'lulu-cache-v1';
const STATIC_ASSETS = [
  '/',
  '/manifest.json',
  '/logo.png',
];

// ─── Install ────────────────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

// ─── Activate ───────────────────────────────────────────────────────────────
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

// ─── Fetch — Network-first, fallback to cache ───────────────────────────────
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests and cross-origin requests
  if (event.request.method !== 'GET') return;
  if (!event.request.url.startsWith(self.location.origin)) return;

  // Skip admin routes — always network
  const url = new URL(event.request.url);
  if (url.pathname.startsWith('/admin')) return;
  if (url.pathname.startsWith('/cart')) return;
  if (url.pathname.startsWith('/checkout')) return;

  event.respondWith(
    fetch(event.request)
      .then((response) => {
        // Cache successful page responses
        if (response.ok && event.request.headers.get('Accept')?.includes('text/html')) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
        }
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});

// ─── Push Notifications ──────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
  let data = {};
  try {
    data = event.data?.json() || {};
  } catch {
    data = { title: 'LULU Couture', body: event.data?.text() || 'You have a new update.' };
  }

  const options = {
    body: data.body || 'Check out the latest from LULU Couture.',
    icon: '/icons/icon-192x192.png',
    badge: '/icons/icon-72x72.png',
    image: data.image || null,
    vibrate: [200, 100, 200],
    tag: data.tag || 'lulu-notification',
    renotify: true,
    data: {
      url: data.url || '/',
      orderId: data.orderId || null,
    },
    actions: data.actions || [
      { action: 'view', title: 'View', icon: '/icons/icon-72x72.png' },
      { action: 'dismiss', title: 'Dismiss' },
    ],
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'LULU Couture', options)
  );
});

// ─── Notification Click ──────────────────────────────────────────────────────
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = event.notification.data?.url || '/';

  if (event.action === 'dismiss') return;

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) return clients.openWindow(targetUrl);
    })
  );
});

// ─── Background Sync (for offline cart actions) ──────────────────────────────
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-cart') {
    event.waitUntil(syncPendingCartActions());
  }
});

async function syncPendingCartActions() {
  // Placeholder — extend if offline cart queuing is needed
  console.log('[SW] Background sync: cart');
}
