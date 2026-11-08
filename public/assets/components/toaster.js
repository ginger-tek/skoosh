const toasts = Vue.reactive([]);
let duration;

export function append(message, opts = {}) {
  const { type = 'info', autoDismiss = true, dismissAfter = 3000, icon = '' } = opts;
  const toast = { icon, message, type, id: Date.now() };
  toasts.push(toast);
  if (autoDismiss)
    setTimeout(() => dismiss(toast.id), dismissAfter);
}

export function dismiss(id) {
  const el = document.getElementById('toast-' + id);
  if (!el) return;
  el.classList.add('dismiss');
  if (!duration)
    duration = parseFloat(window.getComputedStyle(el).transitionDuration) * 1000;
  setTimeout(
    () => toasts.splice(toasts.findIndex(t => t.id === id), 1),
    duration
  );
}

export default {
  template: `<div :class="['toast', 'toast-' + position]">
    <div v-for="toast in toasts" :key="toast.id" :id="'toast-' + toast.id"
     :class="['alert', 'alert-dismissible', 'alert-' + toast.type]" role="alert">
      <i v-if="toast.icon" :class="['bi', toast.icon]"></i> <span v-html="toast.message"></span>
      <button type="button" class="alert-close" aria-label="Close" @click="dismiss(toast.id)">
        <i class="bi bi-x" style="font-size:1.5rem"></i>
      </button>
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