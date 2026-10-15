self.addEventListener('install', () => {
  console.log('[sw] installing...')
  self.skipWaiting()
})

self.addEventListener('activate', (event) => {
  console.log('[sw] activating...')
  event.waitUntil(
    clients.claim().then(() => console.log('[sw] activated + clients claimed'))
  )
})

self.addEventListener('push', (event) => {
  if (!(self.Notification && self.Notification.permission === 'granted'))
    return
  if (!event.data) return
  const data = event.data.json();
  const notif = {
    body: data.message,
    icon: '/assets/img/favicon.svg',
    data: { url: data.resource_url },
    tag: 'skoosh-notification'
  }
  console.log('[sw] send notification', notif);
  event.waitUntil(
    self.registration.showNotification(data.title || 'Skoosh', notif)
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  if (event.notification.data) {
    event.waitUntil(
      clients
        .matchAll({ type: 'window' })
        .then(clientList => {
          for (const client of clientList) {
            console.log('[sw] client', client)
            if (client.match('/') && 'focus' in client) return client.focus()
          }
          if (clients.openWindow)
            return clients.openWindow(event.notification.data?.url);
        })
    );
  }
});

self.addEventListener('pushsubscriptionchange', (event) => {
  console.log('[sw] subscriber expired, updating db')
  if (!event.oldSubscription) {
    console.log('[sw] no old subscription found');
    return;
  }
  const uint8tob64 = (val) => self.btoa(String.fromCharCode.apply(null, new Uint8Array(val)));
  const getPayload = (subscription) => ({
    endpoint: subscription.endpoint,
    p256dh_key: uint8tob64(subscription.getKey('p256dh')),
    auth_key: uint8tob64(subscription.getKey('auth')),
  });
  event.waitUntil(self.registration.pushManager
    .subscribe(event.oldSubscription.options)
    .then((subscription) =>
      fetch('/notifications/resubscribe', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          old: getPayload(event.oldSubscription),
          new: getPayload(subscription),
        })
      })
    ));
});

self.addEventListener('notificationclose', async (event) => {
  console.log('[sw] close notification', event.notification.tag)
})