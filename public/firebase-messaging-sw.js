// Give the service worker access to Firebase Messaging
importScripts('https://www.gstatic.com/firebasejs/12.7.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/12.7.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker
firebase.initializeApp({
  apiKey: "AIzaSyC0JxElG0utTkRbnfSO9DporVpPjvIbeXc",
  authDomain: "iruycode-final.firebaseapp.com",
  projectId: "iruycode-final",
  storageBucket: "iruycode-final.firebasestorage.app",
  messagingSenderId: "188640663792",
  appId: "1:188640663792:web:3e30555d305d035bde8a35"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
  console.log('[firebase-messaging-sw.js] Mensagem em background:', payload);
  
  const notificationTitle = payload.notification.title || 'Bank Manager';
  const notificationOptions = {
    body: payload.notification.body || 'Nova notificação',
    icon: payload.notification.icon || '/icon.png',
    badge: '/badge.png',
    tag: 'bank-manager-notification',
    data: {
      click_action: payload.data?.click_action || payload.fcmOptions?.link || '/bank-manager'
    }
  };

  self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
  console.log('[firebase-messaging-sw.js] Notification click:', event);
  
  event.notification.close();
  
  const urlToOpen = event.notification.data.click_action || '/bank-manager';
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((windowClients) => {
        // Check if there is already a window open
        for (let i = 0; i < windowClients.length; i++) {
          const client = windowClients[i];
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        // If not, open new window
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
  );
});
