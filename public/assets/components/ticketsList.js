import shared from '../shared.js';

export default {
  template: `<div>
    <div class="bottom-spacing flex gap-small">
      <input @input="query = $event.target.value" type="search" placeholder="Search tickets..." class="bottom-clear">
      <router-link to="/tickets/create" role="button" class="btn btn-primary shrink">
        <i class="bi bi-plus-circle"></i> New Ticket
      </router-link>
    </div>
    <div style="margin-bottom: 2rem">
      <article v-for="ticket in filtered" :key="ticket.id"
        :class="['ticket-item', 'card-light', 'card-' + shared.urgency_classes[ticket.urgency].color]">
        <div :class="['float-right', 'badge', 'badge-' + shared.state_classes[ticket.state]]">
          {{ ticket.state }} <span v-if="ticket.state === 'On Hold'">: {{ ticket.reason }}</span>
        </div>
        <b>
          <i :class="['bi', 'bi-' + shared.urgency_classes[ticket.urgency].icon, 'c-' + shared.urgency_classes[ticket.urgency].color]"></i>
          {{ ticket.summary }}</b>
        <div>{{ ticket.category_name }} <i class="bi bi-arrow-right"></i> {{ ticket.location_name }}</div>
        <router-link :to="'/tickets/' + ticket.id" class="stretch-link"></router-link>
      </article>
    </div>
    <div v-if="data.length > 0 && filtered.length === 0" class="text-center">
      No tickets match your search
    </div>
    <div v-else-if="data.length === 0" class="text-center">
      <h4>All clear!</h4>
      <p>Submit a new ticket to get started</p>
    </div>
  </div>`,
  props: ['data'],
  setup(props) {
    const query = Vue.ref('');

    const filtered = Vue.computed(() => {
      const value = query.value.toLowerCase().trim();
      if (!value) return props.data;
      if (value.includes(':')) {
        const [key, val] = value.split(':', 2).map(s => s.trim());
        return props.data.filter(t => t[key]?.toLowerCase().includes(val));
      }
      return props.data.filter(t =>
        `${t.summary}|${t.state}|${t.category_name}|${t.location_name}`.toLowerCase().includes(value)
      );
    });

    return { query, filtered, shared };
  }
}