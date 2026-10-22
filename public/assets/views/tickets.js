import TicketsList from '../components/ticketsList.js';
import { api } from '../utils.js';

export default {
  components: { TicketsList },
  template: `<tickets-list :data="tickets"></tickets-list>`,
  async setup() {
    const tickets = Vue.ref([]);

    async function fetchTickets() {
      tickets.value = await api('/tickets');
    }

    await fetchTickets();

    return {
      tickets
    };
  }
};