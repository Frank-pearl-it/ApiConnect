import { defineStore } from 'pinia'
import axios from 'axios'

export const useLogoStore = defineStore('logo', {
  state: () => ({
    logos: {} // { vendorName: logoUrl }
  }),

  actions: {
    async getLogo(vendorName) {
      if (!vendorName) return null

      // ✅ Use cached version if already fetched in this session
      if (this.logos[vendorName]) return this.logos[vendorName]

      try {
        const res = await axios.get(
          `https://cdn.brandfetch.io/${encodeURIComponent(vendorName)}?c=1idIsTQbZvsbH_PXNDw`, 
        )

        // ✅ Select the first available logo format
        const logo =
          res.data?.logos?.find(l => l.formats?.length)?.formats?.[0]?.src ||
          res.data?.logos?.[0]?.formats?.[0]?.src ||
          null

        if (logo) this.logos[vendorName] = logo
        return logo
      } catch (err) {
        console.warn(`⚠️ Brandfetch failed for "${vendorName}"`, err.message)
        this.logos[vendorName] = null
        return null
      }
    },

    clearLogos() {
      this.logos = {}
    }
  }
})
