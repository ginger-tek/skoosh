import shared from '../shared.js';
import { api, attachRequiredInputs } from '../utils.js';

export default {
  template: `<form @submit.prevent="createUser">
    <header>
      <h4>Create User</h4>
    </header>
    <div class="alert alert-info" role="alert">
      A default password will be assigned to the new user, which they will be required to change after first login
    </div>
    <div class="flex wrap no-row-gap">
      <label>Username
        <input type="text" v-model="user.username" autocapitalize="none" required>
      </label>
      <label>Display Name
        <input type="text" v-model="user.display_name" required>
      </label>
      <label>Role
        <select name="role" v-model="user.role" required>
          <option value="" disabled>Select role</option>
          <option v-for="role in shared.config.users.roles" :key="role" :value="role">{{ role }}</option>
        </select>
      </label>
    </div>
    <button type="submit" class="btn btn-primary" :disabled="submitting" :aria-busy="submitting">
      <i class="bi bi-floppy"></i> Create
    </button>
  </form>`,
  setup() {
    const submitting = Vue.ref(false);
    const router = VueRouter.useRouter();
    const user = Vue.ref({
      username: '',
      display_name: '',
      role: 'User'
    });

    async function createUser() {
      try {
        submitting.value = true;
        const res = await api('/users', 'POST', user.value);
        router.replace(`/users/${res.id}`)
      } catch (ex) {
        console.error(ex);
        submitting.value = false;
      }
    }

    Vue.onMounted(() => attachRequiredInputs());

    return {
      shared,
      submitting,
      user,
      createUser
    };
  }
};