import TicketsList from '../components/ticketsList.js';
import { api } from '../utils.js';

export default {
  components: { TicketsList },
  template: `<tickets-list :data="tickets"></tickets-list>`,
  async setup() {
    const tickets = Vue.ref([]);

    async function fetchDash() {
      const res = await api('/dash');
      tickets.value = res.tickets;
    }

    await fetchDash();

    return {
      tickets
    };
  }
};