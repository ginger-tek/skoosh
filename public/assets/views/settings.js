import { api, attachRequiredInputs } from '../utils.js';

export default {
  template: `<form @submit.prevent="saveSettings">
    <button class="btn btn-primary" :disabled="saving" :aria-busy="saving" type="submit">
      <i class="bi bi-floppy"></i> Save Settings
    </button>
    <div class="flex wrap bottom-spacing">
      <article class="bottom-clear" data-wrapsize="240">
        <header><b>Locations</b></header>
        <div role="group" v-for="location in settings.locations" :key="location.id">
          <input class="input-sm" type="text" v-model="location.name" required>
          <button data-tooltip="Remove" type="button" class="btn btn-error btn-sm"
            @click="settings.locations.splice(settings.locations.indexOf(location), 1)">
            <i class="bi bi-dash-circle"></i> Remove
          </button>
        </div>
        <form @submit.prevent="appendLocation" class="flex gap-small">
          <input class="input-sm" type="text" name="location" placeholder="New location">
          <button type="submit" class="btn btn-success btn-sm shrink">
            <i class="bi bi-plus-circle"></i> Add
          </button>
        </form>
      </article>
      <article class="bottom-clear" data-wrapsize="240">
        <header><b>Categories</b></header>
        <div role="group" v-for="category in settings.categories" :key="category.id">
          <input class="input-sm" type="text" v-model="category.name" required>
          <button data-tooltip="Remove" type="button" class="btn btn-error btn-sm"
            @click="settings.categories.splice(settings.categories.indexOf(category), 1)">
            <i class="bi bi-dash-circle"></i> Remove
          </button>
        </div>
        <form @submit.prevent="appendCategory" class="flex gap-small">
          <input class="input-sm" type="text" name="category" placeholder="New category">
          <button type="submit" class="btn btn-success btn-sm shrink">
            <i class="bi bi-plus-circle"></i> Add
          </button>
        </form>
      </article>
    </div>
  </form>`,
  async setup() {
    const settings = Vue.ref({
      locations: [],
      categories: []
    });
    const error = Vue.ref('');
    const saving = Vue.ref(false);

    async function fetchSettings() {
      settings.value = await api('/settings');
    }

    async function saveSettings() {
      try {
        error.value = null;
        saving.value = true;
        settings.value = await api('/settings', 'PUT', settings.value);
      } catch (ex) {
        console.error(ex);
        error.value = ex.data.error || 'Failed to save settings';
      } finally {
        saving.value = false;
      }
    }

    function appendLocation(ev) {
      const value = ev.target.location.value.trim()
      if (value) {
        settings.value.locations.push({ id: settings.value.locations.length + 1, name: value });
        ev.target.location.value = '';
      }
    }

    function appendCategory(ev) {
      const value = ev.target.category.value.trim()
      if (value) {
        settings.value.categories.push({ id: settings.value.categories.length + 1, name: value });
        ev.target.category.value = '';
      }
    }
    
    Vue.onMounted(attachRequiredInputs);

    await fetchSettings();

    return {
      settings,
      error,
      saving,
      saveSettings,
      appendLocation,
      appendCategory
    };
  }
};