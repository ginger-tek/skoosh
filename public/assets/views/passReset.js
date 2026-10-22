import PasswordPeek from '../components/passwordPeek.js';
import shared from '../shared.js';
import { api } from '../utils.js';

export default {
  components: { PasswordPeek },
  template: `<form @submit.prevent="resetPassword" :aria-disabled="submitting">
    <div v-if="isNewUser" class="alert alert-warning" role="alert">
      <i class="bi bi-exclamation-triangle"></i> You must reset your password after logging in for the first time
    </div>
    <div v-else class="alert alert-info" role="alert">
      <i class="bi bi-info-circle"></i> Resetting your password will automatically log you out
    </div>
    <header>
      <h4>Reset Password</h4>
    </header>
    <div v-if="error" class="alert alert-error" role="alert">
      <i class="bi bi-exclamation-triangle"></i> {{ error }}
    </div>
    <label>Current Password
      <password-peek v-model="creds.current_password" required></password-peek>
    </label>
    <label>New Password
      <password-peek v-model="creds.new_password" required></password-peek>
    </label>
    <label>Confirm New Password
      <password-peek v-model="creds.confirm_new_password" required></password-peek>
    </label>
    <button type="submit" class="btn btn-primary" :disabled="submitting" :aria-busy="submitting">
      <i class="bi bi-check-circle"></i> Submit
    </button>
  </form>`,
  setup() {
    const creds = Vue.ref({
      current_password: '',
      new_password: '',
      confirm_new_password: ''
    });
    const submitting = Vue.ref(false);
    const error = Vue.ref(null);
    const router = VueRouter.useRouter();

    async function resetPassword() {
      try {
        error.value = null;
        if (creds.value.current_password === creds.value.new_password)
          throw new Error('New password must be different from the current password');
        if (creds.value.new_password !== creds.value.confirm_new_password)
          throw new Error('New password and confirmation do not match');
        submitting.value = true;
        await api('/pass-reset', 'POST', creds.value);
        shared.session = null
        router.replace('/login')
      } catch (ex) {
        console.error(ex);
        submitting.value = false;
        error.value = ex.data.error || 'Failed to reset password';
      }
    }

    const isNewUser = Vue.computed(() => shared.session?.status == 'New');

    return {
      creds,
      submitting,
      error,
      resetPassword,
      isNewUser
    };
  }
};