<template>
  <q-layout view="hHh LpR fFf">
    <q-header class="text-white bg-primary">
      <q-toolbar>
        <!-- Hamburger Menu (Visible on Mobile) -->
        <q-btn dense flat round icon="menu" @click="drawerOpen = !drawerOpen" class="q-mr-sm q-md-none q-lg-none" />

        <q-space />
        <q-btn dense flat round href="https://incidenten.portal-hethouvast.nl" label="Incidenten"
          class="q-mr-sm q-md-none q-lg-none" :align="right" style="margin-right: 20px;" />
        <q-btn dense flat round href="https://betaalapp.portal-hethouvast.nl" label="Betalen"
          class="q-mr-sm q-md-none q-lg-none" :align="right" />

      </q-toolbar>
    </q-header>

    <!-- Navigation Drawer -->
    <!-- Drawer -->
    <q-drawer v-model="drawerOpen" show-if-above side="left" elevated>
      <!-- fixed profile item -->
      <q-list bordered>
        <q-item
          id="profileItem"
          v-ripple
            clickable
            @click="navigateTo({ name: 'gebruikerDetails', params: { id: userProfile.id } })"
          class="q-my-sm"
        >
          <q-item-section class="iconSection">
            <q-avatar color="primary" text-color="white">
              <q-icon class="icons" name="account_circle" />
            </q-avatar>
          </q-item-section>
          <q-item-section class="textSection">
            <q-item-label>{{ userProfile?.name }}</q-item-label>
            <q-item-label caption lines="1">{{ userProfile?.rol?.rolNaam }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>

      <!-- links area -->
      <div ref="linkContainer" style="flex: 1; overflow: hidden;">
        <template v-if="useVirtualScroll">
          <q-virtual-scroll :items="filteredLinksList" v-slot="{ item }" style="height: 100%;">
            <q-item clickable v-ripple @click="navigateTo(item.hName)" class="q-my-sm">
              <q-item-section class="iconSection">
                <q-avatar color="primary" text-color="white">
                  <q-icon class="icons" :name="item.icon" />
                </q-avatar>
              </q-item-section>
              <q-item-section class="textSection">
                <q-item-label>{{ item.title }}</q-item-label>
                <q-item-label caption lines="1" v-html="item.caption" />
              </q-item-section>
              <q-item-section class="activeSection">
                <div :class="{ active: $route.fullPath.includes(item.hName) }" class="rounded-dot" />
              </q-item-section>
            </q-item>
          </q-virtual-scroll>
        </template>

        <template v-else>
          <q-list>
            <q-item v-for="item in filteredLinksList" :key="item.hName" clickable v-ripple
              @click="navigateTo(item.hName)" class="q-my-sm">
              <q-item-section class="iconSection">
                <q-avatar color="primary" text-color="white">
                  <q-icon class="icons" :name="item.icon" />
                </q-avatar>
              </q-item-section>
              <q-item-section class="textSection">
                <q-item-label>{{ item.title }}</q-item-label>
                <q-item-label caption lines="1" v-html="item.caption" />
              </q-item-section>
              <q-item-section class="activeSection">
                <div :class="{ active: $route.fullPath.includes(item.hName) }" class="rounded-dot" />
              </q-item-section>
            </q-item>
          </q-list>
        </template>
      </div>
    </q-drawer>


    <q-page-container>
      <router-view @notification-updated="updateDashboardActionRequired" @checkNotifications="checkNotifications" />
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent } from 'vue'
import EssentialLink from 'components/EssentialLink.vue'
import { get, post } from '../../../resources/js/bootstrap.js'
import { popup } from 'src/boot/custom-mixin.js'

export default defineComponent({
  name: 'MainLayout',
  components: {
    EssentialLink
  },
  data() {
    return {
      drawerOpen: false, // Controls drawer visibility
      dashboardActionRequired: false,
      // Base list (static defaults). We'll override titles/captions reactively in computed.
      linksList: [
        {
          title: 'Overzicht',
          hName: 'dashboard',
          caption: 'Jouw agenda en meldingen',
          icon: 'dashboard'
        }, 
      ],
      userProfile: {}
    }
  },
  computed: {
    filteredLinksList() {
      if (!this.userProfile || !this.userProfile.rol) return []

      const roleName = this.userProfile.rol.rolNaam
      const idRol = this.userProfile?.idRol
      
      const rolePermissions = {
         "Client": ['dashboard', 'contactmomenten', 'afspraken', 'houvastTelefoon', 'instellingen', 'logout'],
      'Begeleider ZZP': ['dashboard', 'rapportages', 'contactmomenten', 'afspraken', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'logout'],
      "Begeleider Houvast": ['dashboard', 'rapportages','contactmomenten', 'afspraken', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'logout'],
      'Klusteam': ['dashboard', 'afspraken','rapportages', 'contactmomenten', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'logout'], //afspraken weghalen? maakt trouwens niet uit want ze kunnen het indirect via gebruiker details zien
      'Wagenpark Beheerder': ['dashboard', 'rapportages','contactmomenten', 'afspraken', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'logout'],
      "Bestuur": ['dashboard', 'rooster', 'contactmomenten','afspraken', 'gebruikers', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'rapportages', 'logout'],
      'Admin': ['dashboard', 'rooster', 'contactmomenten','afspraken', 'gebruikers', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'rapportages', 'logout'],
      'Super Admin': ['dashboard','contactmomenten', 'rooster', 'afspraken', 'gebruikers', 'gebruikerDetails', 'houvastTelefoon', 'wagenpark', 'instellingen', 'rapportages', 'logout'],
      
      }

      return this.linksList
        .filter(link => rolePermissions[roleName]?.includes(link.hName))
        .map(link => {
          // Dynamic title/caption for afspraken based on idRol (3 => purely Afspraken)
          if (link.hName === 'afspraken') {
            const title = idRol === 3 ? 'Afspraken' : 'Rooster, Afspraken & Verlof'
            const caption = idRol === 3 ? 'Beheer afspraken' : 'Beheer rooster, afspraken en verlof'
            return { ...link, title, caption }
          }

          // Dynamic dashboard caption when there are pending notifications
          if (link.hName === 'dashboard') {
            return {
              ...link,
              icon: 'dashboard',
              caption: this.dashboardActionRequired
                ? '<span style="color: orange; font-weight: bold;">Onbehandelde meldingen</span>'
                : 'Jouw agenda en meldingen'
            }
          }

          return link
        })
    }
  },
  methods: {
    navigateTo(to) {
      if (to === 'logout') {
        localStorage.removeItem('profile')
        localStorage.removeItem('token')
        return (window.location.href = '/#/')
      }

      if (!to) return

      const route = typeof to === 'string' ? { name: to } : to

      this.$router.push(route).catch(err => {
        if (err.name !== 'NavigationDuplicated') {
          console.error(err)
        }
      })
    },

    checkNotifications() {
      get('notifications')
        .then(response => {
          this.dashboardActionRequired = response.data.length > 0
        })
        .catch(error => {
          popup(
            'error',
            'Error',
            error.response?.data?.message || 'Fout bij het ophalen van notificaties'
          )
        })
    },

    updateDashboardActionRequired(hasNotifications) {
      this.dashboardActionRequired = hasNotifications
    }
  },
  mounted() {
    this.checkNotifications()
  },
  beforeMount() {
    this.userProfile = JSON.parse(localStorage.getItem('profile'))
  }
})
</script>

<style scoped>
#navList {
  overflow-x: hidden;
}

.iconSection {
  max-width: 20% !important;
}

.textSection {
  max-width: 70% !important;
}

.activeSection {
  max-width: 10% !important;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
}

.rounded-dot {
  display: none;
  position: relative;
  top: 2.2vh;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background-color: var(--q-primary);
}

.active {
  display: inline-block !important;
}

#profileItem {
  border-bottom: 2px solid gray;
}
</style>
