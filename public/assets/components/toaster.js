const toasts = Vue.reactive([]);

export function append(message, opts = {}) {
  const { type = 'info', autoDismiss = true } = opts;
  const toast = { message, type, id: Date.now() };
  toasts.push(toast);
  if (autoDismiss)
    setTimeout(() => dismiss(toast.id), 3000);
}

export function dismiss(id) {
  const tel = document.getElementById('toast-' + id);
  tel.classList.add('dismiss');
  setTimeout(() => toasts.splice(toasts.findIndex(t => t.id === id), 1), 200);
}

export default {
  template: `<div :class="['toast', 'toast-' + position]">
    <div v-for="toast in toasts" :key="toast.id" :id="'toast-' + toast.id"
     :class="['alert', 'alert-dismissible', 'alert-' + toast.type]" role="alert">
      <span v-html="toast.message"></span>
      <button type="button" class="alert-close" aria-label="Close" @click="dismiss(toast.id)">&times;</button>
    </div>
  </div>`,
  props: {
    position: {
      type: String,
      default: 'top-center'
    }
  },
  setup() {
    return {
      toasts,
      dismiss
    }
  }
}