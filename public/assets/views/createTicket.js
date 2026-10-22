import shared from '../shared.js';
import { api, attachRequiredInputs } from '../utils.js';

export default {
  template: `<form @submit.prevent="submitTicket">
    <header>
      <h4>Submit New Ticket</h4>
    </header>
    <article v-if="error" class="alert alert-error" role="alert">
      <i class="bi bi-exclamation-triangle"></i> {{ error }}
    </article>
    <label>Summary
      <input v-model="ticket.summary" maxlength="140" autocomplete="off" required>
    </label>
    <div class="flex wrap no-row-gap">
      <label>Location
        <select v-model="ticket.location_id" required>
          <option v-if="settings.locations.length == 0" value="" disabled>No locations available</option>
          <option v-else value="" disabled selected>Select a location</option>
          <option v-for="location in settings.locations" :key="location.id" :value="location.id">
            {{ location.name }}
          </option>
        </select>
      </label>
      <label>Category
        <select v-model="ticket.category_id" required>
          <option v-if="settings.categories.length == 0" value="" disabled>No categories available</option>
          <option v-else value="" disabled selected>Select a category</option>
          <option v-for="category in settings.categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
      </label>
    </div>
    <div class="grid bottom-spacing">
      <div>
        <label>Urgency</label>
        <div role="group">
          <label v-for="(label, value) in shared.config?.tickets.urgency_levels" :key="value" role="button" class="btn btn-primary">
            <input v-model="ticket.urgency" type="radio" :value="value" hidden>
            {{ label }}
          </label>
        </div>
      </div>
    </div>
    <button type="submit" class="btn btn-primary" :disabled="submitting" :aria-busy="submitting">
      <i class="bi bi-check-circle"></i>Submit
    </button>
  </form>`,
  async setup() {
    const error = Vue.ref(null);
    const router = VueRouter.useRouter();
    const submitting = Vue.ref(false);
    const settings = Vue.ref({
      locations: [],
      categories: []
    });
    const ticket = Vue.ref({
      summary: '',
      location_id: '',
      category_id: '',
      urgency: 2,
    });

    async function submitTicket() {
      try {
        error.value = null;
        submitting.value = true;
        const res = await api('/tickets', 'POST', ticket.value);
        router.replace(`/tickets/${res.id}`)
      } catch (ex) {
        console.error(ex);
        error.value = ex.data.error;
        submitting.value = false;
      }
    }

    Vue.onMounted(() => attachRequiredInputs());
    
    settings.value = await api('/settings');

    return {
      error,
      router,
      submitting,
      settings,
      ticket,
      submitTicket,
      shared
    };
  }
};