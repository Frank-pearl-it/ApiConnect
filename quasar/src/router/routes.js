const permissionByRoute = {
  // routeName -> required permission (null = any logged-in user)
  dashboard: null,
  facturen: 'viewInvoices',
  gebruikers: 'viewUsers',
  producten: 'viewProducts',
  tickets: 'viewTickets'
}

function getProfile() {
  try { return JSON.parse(localStorage.getItem('profile')) || null } catch { return null }
}

function hasPermission(profile, perm) {
  if (!perm) return true
  const perms = profile?.role?.permissions || {}
  return perms[perm] === true
}

function firstAllowedRouteName(profile) {
  const order = ['dashboard', 'facturen', 'gebruikers', 'producten', 'tickets']
  for (const name of order) {
    const need = permissionByRoute[name]
    if (hasPermission(profile, need)) return name
  }
  return 'dashboard'
}

// One guard function used everywhere (prevents loops)
const guard = function (to, from, next) {
  const token = localStorage.getItem('token')
  const profile = getProfile()

  const isAuthRequired = to.matched.some(r => r.meta?.requiresAuth === true)
  const isPublic = to.matched.some(r => r.meta?.public === true)

  // 1) Public routes (login, reset) when already logged in -> bounce to first allowed
  if (isPublic) {
    if (token && profile) {
      const target = firstAllowedRouteName(profile)
      if (to.name !== target) return next({ name: target })
    }
    return next()
  }

  // 2) Protected routes require token + profile
  if (isAuthRequired) {
    if (!token) return next({ name: 'login' })
    if (!profile) return next({ name: 'login' })

    // 3) Permission check by route name
    const need = permissionByRoute[to.name]
    if (!hasPermission(profile, need)) {
      const target = firstAllowedRouteName(profile)
      if (to.name !== target) return next({ name: target }) // avoid loop
      // Already at fallback; allow to avoid infinite redirects
      return next()
    }
  }

  return next()
}

const routes = [
  {
    path: '/',
    meta: { public: true },
    beforeEnter: guard,
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: '', name: 'login', component: () => import('pages/LoginPage.vue') }
    ]
  },
  {
    path: '/resetWachtwoord',
    meta: { public: true },
    beforeEnter: guard,
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: '', name: 'resetWachtwoord', component: () => import('pages/ResetWachtwoord.vue') }
    ]
  },

  {
    path: '/dashboard',
    meta: { requiresAuth: true },
    beforeEnter: guard,
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'dashboard', component: () => import('pages/IndexPage.vue') }
    ]
  },

  {
    path: '/facturen',
    meta: { requiresAuth: true },
    beforeEnter: guard,
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'facturen', component: () => import('pages/FacturenPage.vue') }
    ]
  },
  {
    path: '/gebruikers',
    meta: { requiresAuth: true },
    beforeEnter: guard,
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'gebruikers', component: () => import('pages/GebruikersPage.vue') }
    ]
  },
  {
    path: '/producten',
    meta: { requiresAuth: true },
    beforeEnter: guard,
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'producten', component: () => import('pages/ProductenPage.vue') }
    ]
  },
  {
    path: '/tickets',
    meta: { requiresAuth: true },
    beforeEnter: guard,
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', name: 'tickets', component: () => import('pages/TicketsPage.vue') }
    ]
  },

  // Always last
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes