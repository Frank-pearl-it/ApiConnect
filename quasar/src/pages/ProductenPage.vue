<template>
  <q-page class="q-pa-xl bg-white">
    <div class="content-wrapper">
      <!-- Tabs for product type -->
      <q-tabs v-model="activeTab" class="text-primary q-mb-lg" align="left" dense active-color="primary"
        indicator-color="primary" style="margin-bottom: 10px;">
        <q-tab name="extern" label="Externe producten" />

        <q-tab name="intern" label="Interne producten" />
      </q-tabs> 

      <!-- Tabs Panels -->
      <q-tab-panels v-model="activeTab" animated>
        <q-tab-panel name="intern">
          <ProductenIntern />
        </q-tab-panel>

        <q-tab-panel name="extern">
          <ProductenExtern />
        </q-tab-panel>

      </q-tab-panels>
 
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue'
import ProductenIntern from 'components/ProductenIntern.vue'
import ProductenExtern from 'components/ProductenExtern.vue'

export default defineComponent({
  name: 'ProductenPage',
  components: {
    ProductenIntern,
    ProductenExtern
  }, 
  data() {
    return {
      activeTab: 'extern', 
    }
  },

  methods: {  
  }
})
</script>

<style scoped>
.search-input {
  width: 250px;
}

.text-primary {
  color: #1565c0;
}

.content-wrapper {
  min-height: 80vh !important;
}
</style>
