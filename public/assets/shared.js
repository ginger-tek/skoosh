export default Vue.reactive({
  config: {},
  session: null,
  updateAvailable: false,
  hasToken: function() {
    return document.cookie.includes('expires');
  },
  isAdmin: function() {
    return this.session && this.session.role === 'Admin';
  },
  state_classes: {
    'New': 'success',
    'In Progress': 'info',
    'On Hold': 'warning',
    'Completed': 'secondary'
  },
  urgency_classes: {
    1: { color: 'error', icon: 'exclamation-triangle-fill' },
    2: { color: 'warning', icon: 'exclamation-circle-fill' },
    3: { color: 'info', icon: 'tools' }
  }
});