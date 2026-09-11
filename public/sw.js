/**
 * WorkSphere / Ajath PMT - Service Worker for Web Push Notifications & Offline Caching
 */

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

// Push notification received
self.addEventListener('push', (event) => {
  let data = {
    title: 'WorkSphere Notification',
    body: 'You have a new update in your project.',
    url: '/',
    tag: 'worksphere-general',
  };

  if (event.data) {
    try {
      data = { ...data, ...event.data.json() };
    } catch {
      data.body = event.data.text();
    }
  }

  const options = {
    body: data.body,
    icon: '/favicon.svg',
    badge: '/favicon.svg',
    tag: data.tag || 'worksphere-notification',
    data: {
      url: data.url || '/',
    },
    vibrate: [100, 50, 100],
    actions: [
      {
        action: 'open',
        title: 'Open WorkSphere',
      },
    ],
  };

  event.waitUntil(self.registration.showNotification(data.title, options));
});

// User clicked on notification
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const targetUrl = event.notification.data?.url || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url && 'focus' in client) {
          client.focus();
          if ('navigate' in client) {
            return client.navigate(targetUrl);
          }
          return;
        }
      }
      if (self.clients.openWindow) {
        return self.clients.openWindow(targetUrl);
      }
    })
  );
});
