import shared from '../shared.js';
import { api, toHuman } from '../utils.js';
import NotFound from './notFound.js';
import { append } from '../components/toaster.js';

export default {
  components: { NotFound },
  template: `<div>
    <form @submit.prevent="saveUser" :aria-disabled="saving">
      <header class="flex spaced gap-small">
        <div>
          <small>ID: {{ user.id }}
          </small>
          <h4>User Details</h4>
        </div>
        <div class="flex gap-small">
          <button type="submit" :disabled="saving" :aria-busy="saving" class="btn btn-primary"><i
              class="bi bi-floppy"></i> Save</button>
          <button v-if="!isSelf" type="button" @click="deleteModalRef.showModal()" class="btn btn-error"><i
              class="bi bi-trash"></i>
            Delete</button>
        </div>
      </header>
      <div class="alert alert-warning" v-if="isSelf">
        <i class="bi bi-info-circle"></i> Current user role/status can only be changed via the CLI
      </div>
      <div class="flex wrap no-row-gap">
        <label data-wrapsize="150">Username
          <input type="text" v-model="user.username" autocapitalize="none" required>
        </label>
        <label data-wrapsize="150">Display Name
          <input type="text" v-model="user.display_name" required>
        </label>
        <label data-wrapsize="150">Role
          <input v-if="isSelf" type="text" :value="user.role" readonly disabled>
          <select v-else name="role" v-model="user.role" required>
            <option value="" disabled>Select role</option>
            <option v-for="role in shared.config.users.roles" :key="role" :value="role">{{ role }}</option>
          </select>
        </label>
        <label data-wrapsize="150">Status
          <input v-if="isSelf" type="text" :value="user.status" readonly disabled>
          <select v-else name="status" v-model="user.status" required>
            <option value="" disabled>Select status</option>
            <option v-for="status in shared.config.users.statuses" :key="status" :value="status">{{ status }}</option>
          </select>
        </label>
      </div>
    </form>
    <hr>
    <small class="block bottom-spacing">
      Created by
      {{ user.created_by_display_name }} on
      {{ toHuman(user.created_at) }}<br>
      <span v-if="user.updated_at">
        Last updated by
        {{ user.updated_by_display_name }} on
        {{ toHuman(user.updated_at) }}
      </span>
    </small>
    <dialog v-if="!isSelf" ref="deleteModalRef">
      <article>
        <header>
          <b>Confirm Delete</b>
        </header>
        <form @submit.prevent="deleteUser" :aria-disabled="deleting">
          <p>Are you sure you want to delete this user?</p>
          <div class="flex">
            <button type="button" @click="deleteModalRef.close()" class="btn btn-secondary bottom-clear">
              <i class="bi bi-x-circle"></i> Cancel
            </button>
            <button type="submit" class="btn btn-error bottom-clear" :disabled="deleting" :aria-busy="deleting">
              <i class="bi bi-trash"></i> Delete
            </button>
          </div>
        </form>
      </article>
    </dialog>
    <not-found v-if="notFound">
      User not found
    </not-found>
  </div>`,
  async setup() {
    const route = VueRouter.useRoute();
    const router = VueRouter.useRouter();
    const user = Vue.ref({
      id: null,
      role: 'User',
      status: 'New',
      username: '',
      display_name: '',
      created_at: null,
      created_by_display_name: null,
      updated_at: null,
      updated_by_display_name: null
    });
    const saving = Vue.ref(false);
    const notFound = Vue.ref(false);
    const error = Vue.ref(null);
    const deleteModalRef = Vue.ref(null);
    const deleting = Vue.ref(false);

    async function fetchUser() {
      try {
        user.value = await api(`/users/${route.params.id}`);
      } catch (ex) {
        console.error(ex);
        if (ex.status === 404)
          notFound.value = true;
        else
          error.value = ex.data.error || 'Failed to fetch user';
      }
    }

    async function saveUser() {
      try {
        error.value = null;
        saving.value = true;
        user.value = await api(`/users/${user.value.id}`, 'PUT', user.value);
        append('User saved successfully', { type: 'success' });
      } catch (ex) {
        console.error(ex);
        error.value = ex.data.error || 'Failed to save user';
      } finally {
        saving.value = false;
      }
    }

    async function deleteUser() {
      try {
        deleting.value = true;
        await api(`/users/${route.params.id}`, 'DELETE');
        deleteModalRef.value.close();
        router.replace('/users');
      } catch (ex) {
        console.error(ex);
        error.value = ex.data.error || 'Failed to delete user';
      } finally {
        deleting.value = false;
      }
    }

    const isSelf = Vue.computed(() => user.value?.id === shared.session?.id);

    await fetchUser();

    return {
      user,
      saving,
      notFound,
      error,
      deleteModalRef,
      deleting,
      isSelf,
      saveUser,
      deleteUser,
      shared,
      toHuman
    };
  }
};