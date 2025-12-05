import { defineStore } from 'pinia'
import { get } from '../bootstrap'

export const useExternalProductsStore = defineStore('externalProducts', {
  state: () => ({
    products: [],
    vendors: [],
    loading: false,
    lastFetched: null,
  }),

  getters: {
    isLoaded: (state) => state.products.length > 0,
  },

  actions: {
    async fetchProducts(force = false) {
      if (this.isLoaded && !force) return this.products

      this.loading = true
      try {
        const res = await get('/pax8/products')
        this.products = res.data.data?.data || res.data.data || []
        this.lastFetched = new Date()

        // Extract unique vendor names
        const vendors = [...new Set(this.products.map(p => p.vendorName).filter(Boolean))]
        this.vendors = vendors.sort((a, b) => a.localeCompare(b, 'nl', { sensitivity: 'base' }))

        return this.products
      } finally {
        this.loading = false
      }
    },

    filterProducts({ search = '', vendor = null,  }) {
      const term = search.toLowerCase()
      return this.products.filter(p => {
        const matchesSearch = p.name?.toLowerCase().includes(term)
        const matchesVendor = vendor ? p.vendorName === vendor : true
        return matchesSearch && matchesVendor && matchesActive
      })
    },

    clearProducts() {
      this.products = []
      this.vendors = []
      this.lastFetched = null
    }
  }
})
