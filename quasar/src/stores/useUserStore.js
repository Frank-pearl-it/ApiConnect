import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
  state: () => ({
    profile: JSON.parse(localStorage.getItem('profile')) || null,
    loading: false
  }),

  getters: {
    isLoggedIn: (state) => !!state.profile,
    role: (state) => state.profile?.role?.name || null,
    permissions: (state) => state.profile?.role?.permissions || {},
  },

  actions: {
    // Refresh store from localStorage (useful after login or page reload)
    loadFromLocalStorage() {
      const data = localStorage.getItem('profile')
      this.profile = data ? JSON.parse(data) : null
    },

    // Save to localStorage (call this right after login)
    setProfile(profile) {
      this.profile = profile
      localStorage.setItem('profile', JSON.stringify(profile))
    },

    hasPermission(permission) {
      return this.profile?.role?.permissions?.[permission] === true
    },

    // Logout / clear
    clearProfile() {
      this.profile = null
      localStorage.removeItem('profile')
    }
  }
})
