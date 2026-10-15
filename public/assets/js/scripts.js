export function attachSubmitLoading(form) {
  form.addEventListener('submit', function () {
    const btn = form.querySelector('[type="submit"]');
    form.style.pointerEvents = 'none';
    form.style.opacity = '.75';
    btn.setAttribute('aria-busy', 'true');
    btn.disabled = true;
  });
}

export function attachPeekPassword(button) {
  const input = button.previousElementSibling;
  const ondown = () => {
    if (input && input.type === 'password')
      input.type = 'text';
  };
  const onup = () => {
    if (input && input.type === 'text')
      input.type = 'password';
  };
  button.addEventListener('mousedown', ondown);
  button.addEventListener('touchstart', ondown);
  button.addEventListener('mouseup', onup);
  button.addEventListener('mouseleave', onup);
  button.addEventListener('touchend', onup);
}

export function attachSearchFilter(input, selector, empty = null) {
  input.addEventListener('input', function () {
    const items = document.querySelectorAll(selector);
    const query = input.value.toLowerCase();
    items.forEach(item => {
      item.style.display = item.textContent.toLowerCase().includes(query)
        ? 'block'
        : 'none';
    });
    if (empty)
      empty.style.display = Array.from(items).every(item => item.style.display === 'none')
        ? 'block'
        : 'none';
  });
}

export function attachRequiredInputs() {
  const inputs = document.querySelectorAll('[required]');
  inputs.forEach(input => {
    input.addEventListener('invalid', () => input.setAttribute('aria-invalid', 'true'));
    input.addEventListener('input', () => input.removeAttribute('aria-invalid'));
  });
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

export async function subscribeToNotifications(sub) {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    console.warn('Push notifications are not supported in this browser.');
    return;
  }
  try {
    const swUrl = new URL('/assets/js/sw.js', window.location.href).href;
    const registration = await navigator.serviceWorker.register(swUrl, { type: 'module' });
    console.log('Service Worker registered with scope:', registration.scope);
    const checkPermissions = await navigator.permissions.query({ name: 'notifications' });
    if (checkPermissions.state !== 'granted') {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
        console.warn('Push notifications permission not granted.');
        return;
      }
    }
    const subscription = await registration.pushManager.getSubscription();
    if (!subscription) {
      const { publicKey } = await fetch('/api/notifications/config').then(res => res.json());
      const sub = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey)
      });
      console.log('Push subscription successful:', sub);
      await fetch('/api/notifications/subscribe', {
        method: 'POST',
        body: JSON.stringify(sub),
        headers: {
          'Content-Type': 'application/json'
        }
      });
      console.log('Push subscription sent to server successfully.');
    }
  } catch (error) {
    console.error('Service Worker registration failed:', error);
    return;
  }
}