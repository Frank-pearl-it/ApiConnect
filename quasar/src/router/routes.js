const routes = [
  {
    path: '/',
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: '', name: 'login', component: () => import('pages/LoginPage.vue') }
    ]
  },
  {
    path: '/resetWachtwoord',
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: '', name: 'resetWachtwoord', component: () => import('pages/ResetWachtwoord.vue') }
    ]
  },

  {
    path: '/dashboard',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'dashboard', component: () => import('pages/IndexPage.vue') }
    ]
  },

  {
    path: '/facturen',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'facturen', component: () => import('pages/FacturenPage.vue') }
    ]
  },
  {
    path: '/gebruikers',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'gebruikers', component: () => import('pages/GebruikersPage.vue') }
    ]
  },
  {
    path: '/producten',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'producten', component: () => import('pages/ProductenPage.vue') }
    ]
  },
  {
    path: '/tickets',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'tickets', component: () => import('pages/TicketsPage.vue') }
    ]
  },

  // Always leave this as last one
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
