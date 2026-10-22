import router from './router.js'
import shared from './shared.js'
import { api } from './utils.js'
import App from './app.js'

if (shared.hasToken())
  shared.session = await api('/session');

Vue.createApp(App)
  .use(router)
  .mount('#app')