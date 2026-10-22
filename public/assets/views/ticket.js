import shared from '../shared.js';
import { toHuman, api, attachRequiredInputs, attachInvalid } from '../utils.js';
import NotFound from './notFound.js';
import { append } from '../components/toaster.js';

export default {
  components: { NotFound },
  template: `<div v-if="!notFound">
    <form @submit.prevent="saveTicket">
      <header class="flex gap-small spaced">
        <div>
          <small>#{{ ticket.id }}</small>
          <h4>Ticket Details</h4>
        </div>
        <div class="flex gap-small">
          <button type="submit" :aria-busy="saving" :disabled="saving" class="btn btn-primary"><i
              class="bi bi-floppy"></i> Save</button>
          <button v-if="shared.isAdmin()" type="button" @click="deleteModalRef.showModal()" class="btn btn-error"><i
              class="bi bi-trash"></i> Delete</button>
        </div>
      </header>
      <div class="grid">
        <div>
          <label>Summary
            <input type="text" v-model="ticket.summary" required>
          </label>
          <label>Details
            <textarea v-model="ticket.details" placeholder="Add additional details here..."></textarea>
          </label>
          <div class="flex wrap no-row-gap">
            <label>Assignee
              <select v-model="ticket.assignee_id" :disabled="!shared.isAdmin()" required>
                <option v-if="assignees.length === 0" value="" disabled>No users available</option>
                <option v-else value="" disabled>Select an assignee</option>
                <option v-for="assignee in assignees" :key="assignee.id" :value="assignee.id">
                  {{ assignee.display_name }}
                </option>
              </select>
            </label>
            <label>State
              <select v-model="ticket.state" :disabled="!shared.isAdmin()" required>
                <option v-for="state in shared.config.tickets.states" :key="state" :value="state">
                  {{ state }}
                </option>
              </select>
            </label>
            <label v-if="ticket.state == 'On Hold'">
              Hold Reason
              <select v-model="ticket.reason" name="reason" required>
                <option v-if="shared.config.tickets.reasons.length === 0" value="" disabled>No reasons available
                </option>
                <option v-else value="" disabled>Select a reason</option>
                <option v-for="reason in shared.config.tickets.reasons" :key="reason" :value="reason">
                  {{ reason }}
                </option>
              </select>
            </label>
          </div>
          <div class="flex">
            <label>Location
              <select v-model="ticket.location_id" required>
                <option v-if="settings.locations.length === 0" value="" disabled>No locations available</option>
                <option v-else value="" disabled>Select a location</option>
                <option v-for="location in settings.locations" :key="location.id" :value="location.id">
                  {{ location.name }}
                </option>
              </select>
            </label>
            <label>Category
              <select v-model="ticket.category_id" required>
                <option v-if="settings.categories.length === 0" value="" disabled>No categories available</option>
                <option v-else value="" disabled>Select a category</option>
                <option v-for="category in settings.categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </label>
          </div>
          <div class="grid">
            <div>
              <label>Urgency</label>
              <div role="group">
                <label v-for="(label, level) in shared.config.tickets.urgency_levels" :key="level" role="button"
                  class="btn btn-primary">
                  <input name="urgency" type="radio" :value="level" v-model="ticket.urgency" hidden>
                  {{ label }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="bottom-spacing">
          <div style="margin-bottom:.25rem">Notes</div>
          <article v-for="(note) in ticket.notes" :key="note.id">
            <header class="bottom-spacing">
              <span><i class="bi bi-person-circle"></i>
                {{ note.created_by_display_name }}
              </span>
              <small class="float-right">
                {{ toHuman(note.created_at) }}
              </small>
            </header>
            <p class="bottom-clear">
              {{ note.content }}
            </p>
          </article>
          <textarea v-model="newNote" placeholder="Add a note..."></textarea>
        </div>
      </div>
    </form>
    <hr>
    <small class="block bottom-spacing">
      Created by
      {{ ticket.created_by_display_name }} on
      {{ toHuman(ticket.created_at) }}<br>
      <span v-if="ticket.updated_at">
        Last updated by
        {{ ticket.updated_by_display_name }} on
        {{ toHuman(ticket.updated_at) }}
      </span>
    </small>
    <dialog v-if="shared.isAdmin()" ref="deleteModalRef">
      <article>
        <header>
          <b>Confirm Delete</b>
        </header>
        <form @submit.prevent="deleteTicket" :class="{ busy: deleting }">
          <input type="hidden" name="csrf" value="<?= $csrf ?>">
          <p>Are you sure you want to delete this ticket?</p>
          <div class="flex">
            <button type="button" @click="deleteModalRef.close()" class="btn btn-secondary bottom-clear"><i
                class="bi bi-x-circle"></i> Cancel</button>
            <button type="submit" :aria-busy="deleting" :disabled="deleting" class="btn btn-error bottom-clear"><i
                class="bi bi-trash"></i> Delete</button>
          </div>
        </form>
      </article>
    </dialog>
  </div>
  <not-found v-else>
    That ticket doesn't exist
  </not-found>`,
  async setup() {
    const saving = Vue.ref(false);
    const deleting = Vue.ref(false);
    const assignees = Vue.ref([]);
    const settings = Vue.ref({
      locations: [],
      categories: []
    });
    const ticket = Vue.ref({
      id: '',
      summary: '',
      details: '',
      assignee_id: '',
      state: '',
      reason: '',
      location_id: '',
      category_id: '',
      urgency: 2,
      notes: [],
      created_by_display_name: '',
      created_at: '',
      updated_by_display_name: '',
      updated_at: ''
    });
    const newNote = Vue.ref('');
    const route = VueRouter.useRoute();
    const router = VueRouter.useRouter();
    const deleteModalRef = Vue.ref(null);
    const notFound = Vue.ref(false);
    const error = Vue.ref(null);

    async function fetchTicket() {
      try {
        ticket.value = await api(`/tickets/${route.params.id}`);
      } catch (ex) {
        console.error(ex);
        if (ex.status === 404)
          notFound.value = true;
        else
          error.value = ex.data.error || 'Failed to fetch ticket';
      }
    }

    async function saveTicket() {
      try {
        error.value = null;
        saving.value = true;
        ticket.value = await api(`/tickets/${ticket.value.id}`, 'PUT', {
          ...ticket.value,
          new_note: newNote.value
        });
        newNote.value = '';
        append('Ticket saved successfully', { type: 'success' });
      } catch (ex) {
        console.error(ex);
        error.value = ex.data.error || 'Failed to save ticket';
      } finally {
        saving.value = false;
      }
    }

    async function deleteTicket() {
      try {
        deleting.value = true;
        const res = await api(`/tickets/${ticket.value.id}`, 'DELETE');
        if (res?.success)
          router.replace('/');
      } catch (ex) {
        console.error(ex);
        deleting.value = false;
        error.value = ex.data.error || 'Failed to delete ticket';
      }
    }

    Vue.onMounted(attachRequiredInputs);

    Vue.watch(() => ticket.value.state, (newState) => {
      if (newState == 'On Hold')
        Vue.nextTick(() => attachInvalid(document.querySelector('input[name=reason]')));
    });

    await fetchTicket();
    settings.value = await api('/settings');
    assignees.value = await api('/assignees');

    return {
      shared,
      toHuman,
      ticket,
      newNote,
      assignees,
      settings,
      saving,
      deleting,
      error,
      notFound,
      deleteModalRef,
      saveTicket,
      deleteTicket
    };
  }
};