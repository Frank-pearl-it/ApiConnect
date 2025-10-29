<template>
  <div>
    <div class="row q-col-gutter-md">
      <div
        v-for="p in filteredProducts"
        :key="p.id"
        class="col-6 col-sm-4 col-md-3"
      >
        <q-card flat bordered class="flex flex-center q-pa-lg text-grey-6 product-card">
          <q-icon name="inventory_2" size="60px" />
          <div class="q-mt-sm text-center">{{ p.name }}</div>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'ProductenIntern',

  props: {
    search: {
      type: String,
      default: ''
    }
  },

  data() {
    return {
      products: [
        { id: 1, name: 'Office Licentie' },
        { id: 2, name: 'Printer Toner' },
        { id: 3, name: 'VPN Configuratie' },
        { id: 4, name: 'Beveiligingspakket' }
      ]
    }
  },

  computed: {
    filteredProducts() {
      const term = this.search?.toLowerCase() || ''
      return this.products.filter(p =>
        p.name.toLowerCase().includes(term)
      )
    }
  }
})
</script>

<style scoped>
.product-card {
  transition: transform 0.2s ease; 
}
.product-card:hover {
  transform: translateY(-3px);
}
</style>
