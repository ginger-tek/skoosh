import { api } from '../utils.js';

export default {
  template: `<div>
    <div class="bottom-spacing grid">
      <router-link to="/users/create" role="button" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create User</router-link>
    </div>
    <article v-for="user in users" :key="user.id">
      <i class="bi bi-person-circle"></i> <b>{{ user.username }}</b>
      <i v-if="user.role == 'Admin'" style="float:right" class="bi bi-shield-shaded"></i>
      <router-link :to="'/users/' + user.id" class="stretch-link"></router-link>
    </article>
  </div>`,
  async setup() {
    const users = Vue.ref([]);
    
    async function fetchUsers() {
      users.value = await api('/users');
    }
    
    await fetchUsers();

    return {
      users
    };
  }
};