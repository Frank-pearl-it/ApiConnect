<template>
  <q-page class="q-pa-xl bg-white">
    <div class="content-wrapper">
      <!-- Tabs for product type -->
      <q-tabs
        v-model="activeTab"
        class="text-primary q-mb-lg"
        align="left"
        dense
        active-color="primary"
        indicator-color="primary"
      >
        <q-tab name="intern" label="Interne producten" />
        <q-tab name="extern" label="Externe producten" />
      </q-tabs>

      <!-- Toolbar with Filter + Search -->
      <div class="row justify-between items-center q-mb-md">
        <div class="col-auto">
          <q-btn
            icon="filter_list"
            label="Filter"
            color="primary"
            flat
            @click="openFilter = true"
          />
        </div>

        <div class="col-auto">
          <q-input
            dense
            outlined
            debounce="300"
            v-model="search"
            placeholder="Zoek producten..."
            class="search-input"
          >
            <template #prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </div>
      </div>

      <!-- Tabs Panels -->
      <q-tab-panels v-model="activeTab" animated>
        <q-tab-panel name="intern">
          <ProductenIntern :search="search" />
        </q-tab-panel>

        <q-tab-panel name="extern">
          <ProductenExtern :search="search" />
        </q-tab-panel>
      </q-tab-panels>

      <!-- Filter Dialog -->
      <q-dialog v-model="openFilter" persistent>
        <q-card style="min-width: 350px">
          <q-card-section class="text-h6 text-primary">
            Filters
          </q-card-section>

          <q-card-section>
            <q-select
              v-model="selectedCategory"
              :options="categoryOptions"
              label="Categorie"
              outlined
              dense
            />
            <q-toggle
              v-model="showOnlyActive"
              label="Toon alleen actieve producten"
              color="primary"
              class="q-mt-md"
            />
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Annuleren" v-close-popup />
            <q-btn color="primary" label="Toepassen" v-close-popup @click="applyFilter" />
          </q-card-actions>
        </q-card>
      </q-dialog>
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
      activeTab: 'intern',
      search: '',
      openFilter: false,
      selectedCategory: null,
      showOnlyActive: false,
      categoryOptions: ['Software', 'Hardware', 'Licenties', 'Overig']
    }
  },

  methods: {
    applyFilter() {
      console.log('Filters applied:', {
        selectedCategory: this.selectedCategory,
        showOnlyActive: this.showOnlyActive
      })
    }
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
