/**
 * WorkSphere / Ajath PMT - Web Push Notification Service
 * Integrates Service Worker, Push API, and desktop Notifications.
 */

export interface PushSubscriptionData {
  userId: string;
  endpoint: string;
  keys?: {
    p256dh?: string;
    auth?: string;
  };
  subscribedAt: string;
}

const PUSH_SUBSCRIPTIONS_STORAGE_KEY = 'worksphere_push_subscriptions';

export function isPushSupported(): boolean {
  return typeof window !== 'undefined' && 'serviceWorker' in navigator && 'Notification' in window;
}

export function getPushPermissionStatus(): NotificationPermission | 'unsupported' {
  if (!isPushSupported()) return 'unsupported';
  return Notification.permission;
}

export async function registerServiceWorker(): Promise<ServiceWorkerRegistration | null> {
  if (!isPushSupported()) return null;

  try {
    const registration = await navigator.serviceWorker.register('/sw.js', {
      scope: '/',
    });
    return registration;
  } catch (err) {
    console.warn('Service worker registration failed:', err);
    return null;
  }
}

export async function requestPushPermission(): Promise<NotificationPermission> {
  if (!isPushSupported()) return 'denied';

  try {
    const perm = await Notification.requestPermission();
    if (perm === 'granted') {
      await registerServiceWorker();
    }
    return perm;
  } catch (err) {
    console.warn('Error requesting notification permission:', err);
    return 'denied';
  }
}

export async function subscribeToPushNotifications(userId: string): Promise<boolean> {
  if (!isPushSupported()) return false;

  try {
    const perm = await requestPushPermission();
    if (perm !== 'granted') return false;

    const reg = await registerServiceWorker();
    if (!reg) return false;

    // Local subscription tracking
    const subscriptionData: PushSubscriptionData = {
      userId,
      endpoint: `local-client-${userId}-${Date.now()}`,
      subscribedAt: new Date().toISOString(),
    };

    const subs = getStoredPushSubscriptions();
    const existingIndex = subs.findIndex((s) => s.userId === userId);
    if (existingIndex >= 0) {
      subs[existingIndex] = subscriptionData;
    } else {
      subs.push(subscriptionData);
    }
    localStorage.setItem(PUSH_SUBSCRIPTIONS_STORAGE_KEY, JSON.stringify(subs));

    return true;
  } catch (e) {
    console.warn('Failed to subscribe to push notifications:', e);
    return false;
  }
}

export function getStoredPushSubscriptions(): PushSubscriptionData[] {
  try {
    const raw = localStorage.getItem(PUSH_SUBSCRIPTIONS_STORAGE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

export async function sendLocalNotification(
  title: string,
  body: string,
  linkUrl: string = '/'
): Promise<boolean> {
  if (!isPushSupported()) return false;

  if (Notification.permission !== 'granted') {
    return false;
  }

  try {
    const reg = await navigator.serviceWorker.getRegistration();
    if (reg && 'showNotification' in reg) {
      await reg.showNotification(title, {
        body,
        icon: '/favicon.svg',
        tag: `worksphere-${Date.now()}`,
        data: { url: linkUrl },
      });
      return true;
    }

    // Fallback if SW registration not active
    const notif = new Notification(title, {
      body,
      icon: '/favicon.svg',
    });
    notif.onclick = () => {
      window.focus();
      if (linkUrl) window.location.href = linkUrl;
    };
    return true;
  } catch (e) {
    console.warn('Failed to display local notification:', e);
    return false;
  }
}
