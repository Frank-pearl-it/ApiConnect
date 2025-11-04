<template>
  <div class="q-pa-sm">
    <q-virtual-scroll
      :items="filteredProducts"
      virtual-scroll-item-size="120"
      class="row q-col-gutter-md"
    >
      <template #default="{ item: p }">
        <div class="col-6 col-sm-4 col-md-3">
          <q-card
            flat
            bordered
            class="flex flex-center q-pa-md text-grey-7 product-card"
          >
            <q-icon name="inventory_2" size="60px" />
            <div class="q-mt-sm text-center">
              <div class="text-subtitle1 ellipsis">{{ p.name }}</div>
              <div class="text-caption text-grey">{{ p.vendor?.name || 'Onbekende vendor' }}</div>
              <q-badge
                v-if="p.isActive !== undefined"
                :color="p.isActive ? 'positive' : 'grey-6'"
                :label="p.isActive ? 'Actief' : 'Inactief'"
                class="q-mt-xs"
              />
            </div>
          </q-card>
        </div>
      </template>
    </q-virtual-scroll>

    <!-- Loading state -->
    <div v-if="loading" class="text-center q-pa-md text-grey">
      <q-spinner color="primary" size="30px" />
      <div>Laden...</div>
    </div>

    <!-- Empty state -->
    <div v-if="!loading && filteredProducts.length === 0" class="text-center q-pa-md text-grey">
      Geen producten gevonden.
    </div>
  </div>
</template>

<script>
import { get } from '../../../resources/js/bootstrap';
export default {
  name: 'ProductenExtern',

  props: {
    search: {
      type: String,
      default: ''
    },
    selectedCategory: {
      type: String,
      default: null
    },
    showOnlyActive: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      products: [],
      loading: false,
      error: null
    }
  },

  computed: {
    filteredProducts() {
      const term = this.search?.toLowerCase() || ''
      return this.products.filter(p => {
        const matchesSearch = p.name?.toLowerCase().includes(term)
        const matchesCategory = this.selectedCategory
          ? p.category === this.selectedCategory
          : true
        const matchesActive = this.showOnlyActive
          ? p.isActive === true
          : true
        return matchesSearch && matchesCategory && matchesActive
      })
    }
  },

  methods: {
    async fetchProducts() {
      try {
        this.loading = true
        this.error = null

        // ✅ Fetch from Laravel route: /api/pax8/products
        const response = await get('/pax8/products')

        // Pax8 API may wrap response as data.data
        this.products = response.data.data?.data || response.data.data || []
      } catch (err) {
        console.error('Fout bij ophalen producten:', err)
        this.error = err.message
      } finally {
        this.loading = false
      }
    }
  },

  watch: {
    // Re-fetch if category changes (optional)
    selectedCategory() {
      this.fetchProducts()
    }
  },

  mounted() {
    this.fetchProducts()
  }
}
</script>

<style scoped>
.product-card {
  transition: transform 0.2s ease;
  cursor: pointer;
}
.product-card:hover {
  transform: translateY(-3px);
}
</style>
