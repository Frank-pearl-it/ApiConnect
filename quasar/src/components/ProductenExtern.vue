<template>
  <div class="q-pa-md">

    <!-- Toolbar with Filter + Search -->
    <div class="row justify-between items-center q-mb-md">
      <div class="col-auto">
        <q-btn icon="filter_list" label="Filter" color="primary" flat @click="openFilter = true" />
      </div>

      <div class="col-auto">
        <q-input dense outlined debounce="300" v-model="search" placeholder="Zoek producten..." class="search-input">
          <template #prepend>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
    </div>

    <!-- Filter Dialog -->
    <q-dialog v-model="openFilter" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="text-h6 text-primary">
          Filters
        </q-card-section>

        <q-card-section>
          <q-select v-model="selectedVendor" :options="vendorOptions" label="Vendor" outlined dense clearable use-input
            input-debounce="0" behavior="menu" emit-value map-options>
            <template #option="scope">
              <q-item v-bind="scope.itemProps">
                <q-item-section>{{ scope.opt }}</q-item-section>
              </q-item>
            </template>
          </q-select>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Annuleren" v-close-popup />
          <q-btn color="primary" label="Toepassen" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Products Grid -->
    <div v-if="!loading && paginatedProducts.length > 0" class="row q-col-gutter-md">
      <div v-for="p in paginatedProducts" :key="p.id" class="col-6 col-sm-4 col-md-3">
        <q-card flat bordered class="flex flex-center q-pa-md text-grey-7 product-card">
          <!-- Vendor Logo -->
          <div class="q-mb-sm flex flex-center">
            <q-avatar size="60px">
              <img v-if="logos[p.vendorName]" :src="logos[p.vendorName]" :alt="p.vendorName" />
              <q-icon v-else name="inventory_2" size="40px" color="grey" />
            </q-avatar>
          </div>

          <!-- Product Info -->
          <div class="q-mt-sm text-center full-width">
            <div class="text-subtitle1 ellipsis">{{ p.name }}</div>
            <div class="text-caption text-grey">
              {{ p.vendorName || 'Onbekende vendor' }}
            </div>
            <q-badge v-if="p.isActive !== undefined" :color="p.isActive ? 'positive' : 'grey-6'"
              :label="p.isActive ? 'Actief' : 'Inactief'" class="q-mt-xs" />
          </div>
        </q-card>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="!loading && filteredProducts.length > 0" class="flex flex-center q-mt-lg">
      <q-pagination v-model="currentPage" :max="totalPages" :max-pages="7" direction-links boundary-links
        color="primary" active-color="primary" />
      <div class="q-ml-md text-grey">
        {{ startItem }}–{{ endItem }} van {{ filteredProducts.length }}
      </div>
    </div>

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
<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { get } from '../../../resources/js/bootstrap'
import { useLogoStore } from 'stores/useLogoStore'

// ✅ Initialize Pinia store here — safe in setup()
const logoStore = useLogoStore()

// ─────────────────────────────────────
// Reactive state
// ─────────────────────────────────────
const products = ref([])
const loading = ref(false)
const error = ref(null)

// Filters
const openFilter = ref(false)
const search = ref('')
const selectedVendor = ref(null)

// Pagination
const currentPage = ref(1)
const itemsPerPage = 24

// Vendors and logos
const vendorOptions = ref([])
const logos = ref({})

// ─────────────────────────────────────
// Computed properties
// ─────────────────────────────────────
const filteredProducts = computed(() => {
  const term = search.value?.toLowerCase() || ''
  return products.value.filter(p => {
    const matchesSearch = p.name?.toLowerCase().includes(term)
    const matchesVendor = selectedVendor.value
      ? p.vendorName === selectedVendor.value
      : true
    return matchesSearch && matchesVendor
  })
})

const totalPages = computed(() =>
  Math.ceil(filteredProducts.value.length / itemsPerPage)
)

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredProducts.value.slice(start, start + itemsPerPage)
})

const startItem = computed(() =>
  (currentPage.value - 1) * itemsPerPage + 1
)

const endItem = computed(() =>
  Math.min(currentPage.value * itemsPerPage, filteredProducts.value.length)
)

// ─────────────────────────────────────
// Fetch products
// ─────────────────────────────────────
async function fetchProducts() {
  const startTime = performance.now()
  console.groupCollapsed('%c🔍 [Product Fetch Started]', 'color:#2196f3;font-weight:bold;')
  console.log('🕒 Timestamp:', new Date().toLocaleString())

  try {
    loading.value = true
    error.value = null
    console.info('📡 Fetching products from /pax8/products...')

    const response = await get('/pax8/products')

    console.log('✅ Raw API response:', response)
    console.log('📦 Response keys:', Object.keys(response.data || {}))

    const normalized = response.data.data?.data || response.data.data || []
    if (!Array.isArray(normalized)) {
      console.warn('⚠️ Unexpected response structure — expected array, got:', typeof normalized)
    }

    products.value = normalized
    console.info(`📋 Loaded ${products.value.length} products.`)

    // 🔹 Extract vendor names
    const vendors = [...new Set(products.value.map(p => p.vendorName).filter(Boolean))]
    vendorOptions.value = vendors.sort((a, b) =>
      a.localeCompare(b, 'nl', { sensitivity: 'base' })
    )
    console.info(`🏷️ Found ${vendors.length} unique vendors:`, vendors)

    // ✅ Preload logos
    let fetchedLogos = 0
    let cachedLogos = 0

    for (const vendor of vendors) {
      if (!vendor) {
        console.warn('⚠️ Skipping product with missing vendorName.')
        continue
      }

      if (logos.value[vendor]) {
        cachedLogos++
        continue
      }

      try {
        console.log(`🖼️ Fetching logo for vendor: ${vendor}`)
        const logo = await logoStore.getLogo(vendor)
        if (logo) {
          logos.value[vendor] = logo
          fetchedLogos++
          console.log(`✅ Logo fetched for ${vendor}:`, logo)
        } else {
          console.warn(`⚠️ No logo found for ${vendor}.`)
        }
      } catch (logoError) {
        console.error(`❌ Error fetching logo for ${vendor}:`, logoError)
      }
    }

    console.info(`🧾 Logo summary: fetched=${fetchedLogos}, cached=${cachedLogos}`)
  } catch (err) {
    console.groupCollapsed('%c❌ [Fetch Error]', 'color:red;font-weight:bold;')
    console.error('Error object:', err)
    console.error('Error message:', err.message)
    console.error('Stack trace:', err.stack)
    console.groupEnd()
    error.value = err.message
  } finally {
    const duration = (performance.now() - startTime).toFixed(1)
    console.info(`⏱️ Total time: ${duration} ms`)
    console.groupEnd()
    loading.value = false
  }
}

// ─────────────────────────────────────
// Watchers
// ─────────────────────────────────────
watch(search, () => (currentPage.value = 1))
watch(selectedVendor, () => (currentPage.value = 1))

// ─────────────────────────────────────
// Lifecycle
// ─────────────────────────────────────
onMounted(() => {
  fetchProducts()
})
</script>


<style scoped>
.search-input {
  width: 250px;
}

.product-card {
  transition: transform 0.2s ease;
  cursor: pointer;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.product-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.ellipsis {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  width: 100%;
}
</style>
