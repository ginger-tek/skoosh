import { api, applyUpdate, registerServiceWorker, subscribeToNotifications } from './utils.js';
import shared from './shared.js';
import Toaster from './components/toaster.js';

export default {
  components: { Toaster },
  template: `<header>
    <nav>
      <ul>
        <li><router-link to="/"><img class="logo" src="/favicon.svg"> <b>Skoosh</b></router-link></li>
      </ul>
      <ul>
        <li v-if="!!shared.session">
          <details class="dropdown" @click="$event.target.open = !$event.target.open">
            <summary><i class="bi bi-person-circle"></i> {{ shared.session.display_name }}</summary>
            <ul dir="rtl">
              <template v-if="shared.session && shared.isAdmin()">
                <li dir="ltr">
                  <router-link to="/tickets"><i class="bi bi-receipt"></i> All Tickets</router-link>
                </li>
                <li dir="ltr">
                  <router-link to="/settings"><i class="bi bi-gear-fill"></i> Settings</router-link>
                </li>
                <li dir="ltr">
                  <router-link to="/users"><i class="bi bi-people-fill"></i> Users</router-link>
                </li>
              </template>
              <li dir="ltr">
                <router-link to="/pass-reset"><i class="bi bi-key-fill"></i> Reset Password</router-link>
              </li>
              <li dir="ltr">
                <a style="cursor:pointer" @click="submitLogout()"><i class="bi bi-box-arrow-right"></i> Logout</a>
              </li>
            </ul>
          </details>
        </li>
        <li v-else>
          <router-link to="/login"><i class="bi bi-box-arrow-in-right"></i> Login</router-link>
        </li>
      </ul>
    </nav>
  </header>
  <main>
    <router-view v-slot="{ Component, route }">
      <template v-if="Component">
        <transition name="view-nav-fade" mode="out-in">
          <suspense timeout="0">
            <component :is="Component" :key="route.fullPath" />
            <template #fallback>
              <div aria-busy="true" class="fallback"></div>
            </template>
          </suspense>
        </transition>
      </template>
    </router-view>
    <dialog ref="updateDialog" open v-if="shared.updateAvailable">
      <article>
        <header><b>Update Available</b></header>
        <p>Click reload to apply the update now, or cancel to continue using the current version until next app load.</p>
        <div class="flex">
          <button class="btn btn-primary" @click="applyUpdate">Reload</button>
          <button class="btn btn-secondary" @click="$refs.updateDialog.close()">Cancel</button>
        </div>
      </article>
    </dialog>
  </main>
  <toaster></toaster>`,
  setup() {
    async function submitLogout() {
      try {
        await api('/logout', 'POST');
        shared.session = null;
        window.location.replace('/login');
      } catch (ex) {
        console.error(ex);
      }
    }
    
    Vue.watch(() => shared.session, (newSession) => {
      if (newSession) subscribeToNotifications();
    });

    Vue.onMounted(async () => {
      await registerServiceWorker();
    });

    Vue.onBeforeMount(async () => {
      shared.config = await api('/config');
    });

    return {
      shared,
      applyUpdate,
      submitLogout
    };
  }
};