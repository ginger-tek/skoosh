import PasswordPeek from '../components/passwordPeek.js';
import shared from '../shared.js';
import { api } from '../utils.js';

export default {
  components: { PasswordPeek },
  template: `<form id="loginForm" @submit.prevent="login" style="margin-inline:auto;max-inline-size:320px" :aria-disabled="busy">
    <h1>Login</h1>
    <article v-if="error" class="alert alert-error" role="alert">
      <i class="bi bi-exclamation-triangle"></i> {{ error }}
    </article>
    <label>Username
      <input v-model="creds.username" name="username" autocapitalize="off" required>
    </label>
    <label>Password
      <password-peek v-model="creds.password" name="password" required></password-peek>
    </label>
    <label class="bottom-spacing">
      <input type="checkbox" v-model="creds.remember" name="remember">Remember me for 30 days
    </label>
    <button type="submit" class="btn btn-primary" :disabled="busy" :aria-busy="busy">
      <i class="bi bi-box-arrow-in-right"></i> Login
    </button>
  </form >`,
  setup() {
    const router = VueRouter.useRouter();
    const route = VueRouter.useRoute();
    const error = Vue.ref(null);
    const busy = Vue.ref(false);
    const creds = Vue.ref({
      username: '',
      password: '',
      remember: false
    });

    async function login() {
      try {
        error.value = null;
        busy.value = true;
        const res = await api('/login', 'POST', creds.value);
        shared.session = await api('/session');
        router.replace(route.query.next || res.redirect || '/');
      } catch (ex) {
        console.error(ex);
        busy.value = false;
        error.value = ex?.data?.error || 'Login failed';
      }
    }

    return {
      creds,
      error,
      busy,
      login
    };
  }
};