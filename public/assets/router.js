import shared from './shared.js'

const routes = [
  { path: '/', component: () => import('./views/dash.js') },
  { path: '/tickets/create', component: () => import('./views/createTicket.js') },
  { path: '/tickets/:id', component: () => import('./views/ticket.js') },
  { path: '/tickets', component: () => import('./views/tickets.js'), meta: { admin: 1 } },
  { path: '/settings', component: () => import('./views/settings.js'), meta: { admin: 1 } },
  { path: '/users/create', component: () => import('./views/createUser.js'), meta: { admin: 1 } },
  { path: '/users/:id', component: () => import('./views/user.js'), meta: { admin: 1 } },
  { path: '/users', component: () => import('./views/users.js'), meta: { admin: 1 } },
  { path: '/pass-reset', component: () => import('./views/passReset.js') },
  { path: '/login', component: () => import('./views/login.js') },
  { path: '/unauthorized', component: () => import('./views/unauthorized.js') },
  { path: '/:pathMatch(.*)*', component: () => import('./views/notFound.js') },
]

const router = VueRouter.createRouter({
  history: VueRouter.createWebHistory(),
  routes
})

router.beforeEach((to, from) => {
  if (!shared.session && to.path !== '/login') {
    return `/login${!from.path.match(/^\/login|\/$/) ? `?next=${encodeURIComponent(to.path)}` : ''}`;
  } else if (!shared.isAdmin() && !!to.meta.admin) {
    return '/unauthorized';
  } else if (shared.session?.status == 'New' && to.path !== '/pass-reset') {
    return '/pass-reset';
  } else if (shared.session?.status == 'Active' && to.path === '/login') {
    return '/';
  }
});

export default router;