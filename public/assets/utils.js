import shared from './shared.js';

let serviceWorkerRegistration;

export async function registerServiceWorker() {
  if (!('serviceWorker' in navigator)) return Promise.resolve(null);
  if (serviceWorkerRegistration) return serviceWorkerRegistration;
  const wasControlled = !!navigator.serviceWorker.controller;
  navigator.serviceWorker.addEventListener('controllerchange', () => {
    if (wasControlled) window.location.reload();
  });
  try {
    const registration = await navigator.serviceWorker.register('/sw.js', {
      type: 'module',
      updateViaCache: 'none'
    })
    await navigator.serviceWorker.ready;
    const checkWaiting = () => {
      shared.updateAvailable = !!registration.waiting && !!navigator.serviceWorker.controller;
    };
    const watchInstalling = () => {
      registration.installing?.addEventListener('statechange', checkWaiting);
    };
    registration.addEventListener('updatefound', watchInstalling);
    watchInstalling();
    checkWaiting();
    const checkForUpdate = () => {
      if (document.visibilityState === 'visible')
        registration.update().catch((error) => console.warn('Update check failed:', error));
    };
    document.addEventListener('visibilitychange', checkForUpdate);
    checkForUpdate();
    serviceWorkerRegistration = registration;
  } catch (error) {
    console.error('Service Worker registration failed:', error);
    serviceWorkerRegistration = null;
  }
  return serviceWorkerRegistration;
}

export async function applyUpdate() {
  const registration = await registerServiceWorker();
  registration?.waiting?.postMessage({ type: 'SKIP_WAITING' });
}

export function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i)
    outputArray[i] = rawData.charCodeAt(i);
  return outputArray;
}

export async function subscribeToNotifications() {
  if (!('PushManager' in window)) return null;
  try {
    if (!serviceWorkerRegistration) return;
    const checkPermissions = await navigator.permissions.query({ name: 'notifications' });
    if (checkPermissions.state !== 'granted') {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') return;
    }
    const subscription = await serviceWorkerRegistration.pushManager.getSubscription();
    if (!subscription) {
      const sub = await serviceWorkerRegistration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(shared.config.push_key)
      });
      await api('/notifications/subscribe', 'POST', sub);
    }
  } catch (ex) {
    console.error('Service Worker registration failed:', ex);
  }
}

export async function api(url, method = 'GET', body = null) {
  const res = await fetch('/api' + url, {
    method,
    headers: { 'Content-Type': 'application/json' },
    body: body ? JSON.stringify(body) : null,
    credentials: 'include'
  });
  const data = await res.json();
  if (res.status === 401 && url !== '/login') {
    shared.session = null
    window.location.href = '/login';
  }
  return res.ok ? data : Promise.reject({ data, status: res.status });
};

export function toHuman(timestamp = null) {
  if (!timestamp) return '';
  return luxon.DateTime.fromSeconds(timestamp).toLocaleString(luxon.DateTime.DATETIME_MED);
}

export function attachRequiredInputs() {
  document.querySelectorAll('[required]').forEach(input => attachInvalid(input));
}

export function attachInvalid(el) {
  if (!el) return;
  el.addEventListener('invalid', () => el.setAttribute('aria-invalid', 'true'));
  el.addEventListener('input', () => el.removeAttribute('aria-invalid'));
}
