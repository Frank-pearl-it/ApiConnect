<template>
  <q-layout view="hHh LpR fFf">
    <!-- Header -->
    <q-header class="text-white bg-primary">
      <q-toolbar>
        <!-- Hamburger Menu -->
        <q-btn dense flat round icon="menu" @click="drawerOpen = !drawerOpen" class="q-mr-sm q-md-none q-lg-none" />
        <div class="header-title">Pearl-IT Klantenportaal</div>

        <q-space />

        <!-- Remote Support Links -->
        <div class="row items-center q-gutter-sm">
          <q-btn dense flat color="white" label="AnyDesk" no-caps class="support-btn"
            @click="triggerDownload(anyDeskLink)">
            <q-tooltip>Download AnyDesk</q-tooltip>
          </q-btn>

          <q-btn dense flat color="white" label="TeamViewer" no-caps class="support-btn"
            @click="triggerDownload(teamViewerLink)">
            <q-tooltip>Download TeamViewer</q-tooltip>
          </q-btn>
        </div>


      </q-toolbar>
    </q-header>

    <!-- Navigation Drawer -->
    <q-drawer v-model="drawerOpen" show-if-above side="left" elevated class="column no-wrap">
      <!-- Profile Section -->
      <q-list bordered>
        <q-item id="profileItem" class="q-my-sm">
          <q-item-section class="iconSection">
            <q-avatar color="primary" text-color="white">
              <q-icon class="icons"  size="md" name="account_circle" />
            </q-avatar>
          </q-item-section>
          <q-item-section class="textSection">
            <q-item-label>{{ userProfile?.name }}</q-item-label>
            <q-item-label caption lines="1">{{ userProfile?.role?.name }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>

      <!-- Main Links -->
      <div ref="linkContainer" class="drawer-links">
        <q-list>
          <q-item v-for="item in linksList" :key="item.hName" clickable v-ripple @click="navigateTo(item.hName)"
            class="q-my-sm">
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
      </div>

      <!-- Bottom Section (Contact + Logout) -->
      <div class="q-mt-auto q-pb-sm">
        <q-list>
          <!-- Contact item -->
          <q-item clickable v-ripple ref="contactItem">
            <q-item-section class="iconSection">
              <q-avatar color="primary" text-color="white">
                <q-icon name="contact_support" />
              </q-avatar>
            </q-item-section>
            <q-item-section class="textSection">
              <q-item-label>Contact</q-item-label>
              <q-item-label caption>Bekijk contactinformatie</q-item-label>
            </q-item-section>
            <q-menu anchor="top right" self="top left">
              <q-card flat bordered class="q-pa-md text-left shadow-1 contact-card">
                <div class="text-subtitle1 text-bold text-primary q-mb-sm">Contact</div>
                <div>Email: <span class="text-grey-8">helpdesk@pearl-it.nl</span></div>
                <div>Tel: <span class="text-grey-8">+31 (0)13 - 203 20 78</span></div>
              </q-card>
            </q-menu>
          </q-item>

          <!-- Uitloggen item -->
          <q-item clickable v-ripple @click="logout" class="q-my-sm">
            <q-item-section class="iconSection">
              <q-avatar color="primary" text-color="white">
                <q-icon name="logout" />
              </q-avatar>
            </q-item-section>
            <q-item-section class="textSection">
              <q-item-label>Uitloggen</q-item-label>
              <q-item-label caption>Afmelden van je account</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </div>
    </q-drawer>

    <!-- Page Content -->
    <q-page-container>
      <!-- ✅ Fixed Stable Logo -->
      <!-- <div class="page-logo" :class="{ 'drawer-open': drawerOpen }">
        <img src="~assets/logo-thin.svg" alt="Logo" class="page-logo-img" />
      </div> -->

      <router-view @notification-updated="updateDashboardActionRequired" @checkNotifications="checkNotifications" />
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent } from 'vue'
import { get, post } from '../../../resources/js/bootstrap.js'
import { popup } from 'src/boot/custom-mixin.js'
import { showLoading, hideLoading } from 'src/utils/loading.js'
import { useUserStore } from 'src/stores/useUserStore.js'
export default defineComponent({
  name: 'MainLayout',

  data() {
    return {
      drawerOpen: false,
      dashboardActionRequired: false,
      userProfile: {}
    }
  },

  computed: {
    teamViewerLink() {
      const ua = navigator.userAgent.toLowerCase()
      if (ua.includes('mac')) return 'https://download.teamviewer.com/download/TeamViewer.dmg'
      if (ua.includes('linux')) return 'https://download.teamviewer.com/download/linux/teamviewer_amd64.deb'
      if (ua.includes('android')) return 'https://download.teamviewer.com/download/TeamViewer.apk'
      return 'https://download.teamviewer.com/download/TeamViewer_Setup.exe' // Default: Windows
    },

    anyDeskLink() {
      const ua = navigator.userAgent.toLowerCase()
      if (ua.includes('mac')) return 'https://download.anydesk.com/anydesk.dmg'
      if (ua.includes('linux')) return 'https://download.anydesk.com/anydesk_amd64.deb'
      if (ua.includes('android')) return 'https://download.anydesk.com/anydesk.apk'
      return 'https://download.anydesk.com/AnyDesk.exe' // Default: Windows
    },
    linksList() {
      // Try to get the freshest profile: localStorage > store
      const userStore = useUserStore()
      const profile = this.userProfile?.role ? this.userProfile : userStore.profile

      const permissions = profile?.role?.permissions || {}

      const allLinks = [
        {
          title: 'Overzicht',
          hName: 'dashboard',
          caption: this.dashboardActionRequired
            ? '<span style="color: orange; font-weight: bold;">Je hebt nieuwe meldingen!</span>'
            : 'Jouw agenda en meldingen',
          icon: 'dashboard',
          permission: null
        },
        {
          title: 'Facturen',
          hName: 'facturen',
          caption: 'Bekijk en beheer facturen',
          icon: 'receipt_long',
          permission: 'viewInvoices'
        },
        {
          title: 'Gebruikers',
          hName: 'gebruikers',
          caption: 'Beheer gebruikers en rollen',
          icon: 'people',
          permission: 'viewUsers'
        },
        {
          title: 'Producten',
          hName: 'producten',
          caption: 'Bekijk en beheer producten',
          icon: 'inventory_2',
          permission: 'viewProducts'
        },
        {
          title: 'Tickets',
          hName: 'tickets',
          caption: 'Bekijk en behandel support tickets',
          icon: 'confirmation_number',
          permission: 'viewTickets'
        }
      ]

      // ✅ Filter based on actual permissions (literal keys from backend)
      return allLinks.filter(link => {
        if (!link.permission) return true
        return permissions[link.permission] === true
      })
    }
  },
  watch: {
    'userStore.profile': {
      handler(newVal) {
        if (newVal) {
          this.userProfile = newVal
        }
      },
      deep: true
    }
  },

  methods: {
    navigateTo(to) {
      if (!to) return
      const route = typeof to === 'string' ? { name: to } : to
      this.$router.push(route).catch(err => {
        if (err.name !== 'NavigationDuplicated') console.error(err)
      })
    },

    logout() {
      showLoading('Uitloggen...');
      post('logout')
        .catch(() => { }) // ignore if session already gone
        .finally(async () => {
          // Step 2: Clear all local auth data
          localStorage.removeItem('profile');
          localStorage.removeItem('token');

          // Clear old Laravel cookies
          ['XSRF-TOKEN', 'laravel_session'].forEach(name => {
            document.cookie = `${name}=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT;`;
          });

          // Step 3: Request a *new* CSRF cookie for next login
          try {
            await get('/../sanctum/csrf-cookie');
          } catch (e) {
            console.warn('Kon CSRF-cookie niet vernieuwen:', e);
          }

          // Step 4: Redirect cleanly to login
          hideLoading();
          this.$router.replace({ name: 'login' });
        });
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
    },

    triggerDownload(link) {
      if (!link) return

      // Create invisible <a> tag to trigger download cleanly
      const a = document.createElement('a')
      a.href = link
      a.setAttribute('download', '')
      a.style.display = 'none'

      document.body.appendChild(a)
      a.click()
      document.body.removeChild(a)
    }

  },

  beforeMount() {
    try {
      this.userProfile = JSON.parse(localStorage.getItem('profile')) || {}
    } catch {
      this.userProfile = {}
    }
  }
})
</script>

<style scoped>
.q-drawer {
  display: flex;
  flex-direction: column;
}

.drawer-links {
  flex: 1;
  overflow-y: auto;
  padding-bottom: 10px;
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

/* Contact Popup */
.contact-card {
  width: 220px;
  border-radius: 12px;
}

.text-primary {
  color: #1565c0;
}

.q-header {
  right: 0px;
}

/* ✅ Always reserve scrollbar space so nothing shifts */
html {
  scrollbar-gutter: stable;
}

/* ✅ Fixed logo: identical position & size on every page */
/* ✅ Fixed logo: identical position & size on every page */
.page-logo {
  position: fixed;
  top: 42%;
  /* slightly above vertical center */
  left: 50vw;
  /* center using viewport width */
  transform: translate(-37%, -50%);
  /* moved ~10% to the right */
  z-index: 1;
  pointer-events: none;
}


.page-logo-img {
  width: 1440px;
  /* fixed pixel size (no change on scrollbar toggle) */
  max-width: 90vw;
  /* responsive for smaller screens */
  height: auto;
}

/* ✅ Fixed logo baseline: centered when drawer closed */
.page-logo {
  position: fixed;
  top: 42%;
  left: 50vw;
  transform: translate(-50%, -50%);
  /* fully centered */
  z-index: 1;
  pointer-events: none;
  transition: transform 0.3s ease;
  /* smooth movement */
}

/* ✅ When drawer is open, shift slightly to the right */
.page-logo.drawer-open {
  transform: translate(-37%, -50%);
  /* same offset you liked before */
}

.header-title {
  font-size: 1rem;
  font-weight: bold;
}

.support-btn {
  font-size: 1rem;
  text-decoration: underline;
}
</style>
