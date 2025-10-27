const guard = function (to, from, next) {
  const bearerToken = localStorage.getItem('token');
  const userProfile = JSON.parse(localStorage.getItem('profile'));

  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!bearerToken) {
      return next('/'); // Redirect to login if not authenticated
    }

    if (!userProfile || !userProfile.role) {
      return next('/'); // Redirect to login if no valid profile
    }

    const userRole = userProfile.role.name.trim(); // Trim in case of extra spaces

    // Role-based permissions
    const rolePermissions = {
      "Client": ['dashboard', 'afspraken', 'houvastTelefoon', 'instellingen', 'contactmomenten', 'gebruikerDetails'],
      'Begeleider ZZP': ['dashboard', 'rapportages', 'afspraken', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'contactmomenten'],
      "Begeleider Houvast": ['dashboard', 'rapportages','afspraken','gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'contactmomenten'],
      'Klusteam': ['dashboard', 'afspraken','rapportages', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'contactmomenten'], //afspraken weghalen? maakt trouwens niet uit want ze kunnen het indirect via gebruiker details zien
      'Wagenpark Beheerder': ['dashboard', 'rapportages','afspraken', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'contactmomenten'],
      "Bestuur": ['dashboard', 'rooster', 'afspraken', 'gebruikers', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'rapportages', 'contactmomenten'],
      'Admin': ['dashboard', 'rooster', 'afspraken', 'gebruikers', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'rapportages', 'contactmomenten'],
      'Super Admin': ['dashboard', 'rooster', 'afspraken', 'gebruikers', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'rapportages', 'contactmomenten'],
      
    };

    // Get allowed routes for the current role
    const allowedRoutes = rolePermissions[userRole] || [];
  if (to.name === 'gebruikerDetails') {
      if (['Bestuur', 'Admin', 'Super Admin'].includes(userRole)) {
        // Always allowed
      } else {
        // Only allowed if the :id matches the logged-in user
        if (String(to.params.id) !== String(userProfile.id)) {
          return next('/'); // deny access
        }
      }
    }
    if (!allowedRoutes.includes(to.name)) {
      return next('/'); // Redirect unauthorized users to the dashboard
    }
  }

  next(); // Allow navigation if all checks pass
};

const routes = [
  {
    path: '/',
    beforeEnter: guard,
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: '', name: 'login', component: () => import('pages/LoginPage.vue') }
    ]
  },  
  {
    path:  '/resetWachtwoord',
    beforeEnter: guard,
    component: () => import('layouts/BlankLayout.vue'),
    children: [
      { path: '', name: 'resetWachtwoord', component: () => import('pages/ResetWachtwoord.vue') }
    ]
  },
  // Always leave this as last one
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes;
