const CACHE_NAME = 'skoosh-cache-v1.0.0';
const ASSETS = [
  './',
  './appicon.svg',
  './favicon.svg',
  './assets/components/ticketsList.js',
  './assets/components/passwordPeek.js',
  './assets/components/toaster.js',
  './assets/views/createTicket.js',
  './assets/views/createUser.js',
  './assets/views/dash.js',
  './assets/views/login.js',
  './assets/views/notFound.js',
  './assets/views/passReset.js',
  './assets/views/settings.js',
  './assets/views/ticket.js',
  './assets/views/tickets.js',
  './assets/views/unauthorized.js',
  './assets/views/user.js',
  './assets/views/users.js',
  './assets/app.js',
  './assets/main.js',
  './assets/router.js',
  './assets/shared.js',
  './assets/utils.js',
  './assets/styles.css',
  './manifest.json',
  'https://unpkg.com/vue@3.5.44/dist/vue.global.js',
  'https://unpkg.com/vue-router@5.4.0/dist/vue-router.global.js',
  'https://unpkg.com/luxon@3.7.2/build/global/luxon.min.js',
  'https://unpkg.com/@digicreon/mucss@1.4.9/dist/mu.violet.css',
  'https://unpkg.com/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css'
]

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS.map((asset) => new Request(asset, { cache: 'reload' })));
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key.startsWith('skoosh-cache-') && key !== CACHE_NAME) return caches.delete(key);
        })
      )
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') self.skipWaiting();
});

self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  if (url.pathname.startsWith('/api') && !url.pathname.startsWith('/api/config')) {
    event.respondWith(fetch(event.request));
    return;
  }
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request);
    })
  );
});

self.addEventListener('push', (event) => {
  if (!event.data) return
  const data = event.data.json();
  const options = {
    body: data.message,
    icon: './appicon.svg',
    badge: './appicon.svg',
    data: { url: data.resource_url },
    tag: 'skoosh-notification'
  };
  event.waitUntil(
    self.registration.showNotification(data.title || 'Skoosh', options)
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = event.notification.data.url;
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then((clientList) => {
      for (const client of clientList)
        if (client.url === url && 'focus' in client)
          return client.focus();
      if (clients.openWindow)
        return clients.openWindow(url);
    })
  );
});

self.addEventListener('pushsubscriptionchange', (event) => {
  if (!event.oldSubscription) return;
  event.waitUntil(
    self.registration.pushManager
      .subscribe(event.oldSubscription.options)
      .then((new_subscription) => api('/notifications/resubscribe', 'POST', {
        old_endpoint: event.oldSubscription.endpoint,
        new_subscription
      })
      )
  );
});