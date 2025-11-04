<template>
  <q-page class="q-pa-xl bg-white">
 

    <!-- Foreground Content -->
    <div>

      <!-- Top Section (Notifications + Contact) -->
      <div class="row justify-between items-start q-gutter-lg">

        <!-- Left: Notifications -->
        <div class="col-12 col-md-6">
          <q-card flat bordered class="q-pa-md shadow-1">
            <div class="text-subtitle1 text-bold text-primary q-mb-sm">
              Notificaties
            </div>

            <q-list separator>
              <q-item clickable>
                <q-item-section>
                  <q-item-label>Ticket melding</q-item-label>
                  <q-item-label caption>
                    Er is een melding op ticket over "Outlook problemen"
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>

        <!-- Right: Contact Info -->
        <div class="col-12 col-md-3">
          <q-card flat bordered class="q-pa-md text-left shadow-1">
            <div class="text-subtitle1 text-bold text-primary q-mb-sm">Contact</div>
            <div>Email: <span class="text-grey-8">helpdesk@pearl-it.nl</span></div>
            <div>Tel: <span class="text-grey-8">+31 (0)13 - 203 20 78</span></div>
          </q-card>
        </div>
      </div>

      <q-separator class="q-my-xl" inset />
      <!-- Aanbiedingen Section at Bottom -->
      <div id="aanbiedingen-section" class="q-mt-xl">
        <div class="text-h6 text-primary text-bold q-mb-sm">Aanbiedingen</div>
        <div class="row q-col-gutter-md">
          <div v-for="i in 12" :key="i" class="col-6 col-sm-4 col-md-3">
            <q-card flat bordered class="flex flex-center q-pa-lg text-grey-6 aanbieding-card">
              <q-icon name="inventory_2" size="80px" />
            </q-card>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'IndexPage'
})
</script>

<style scoped>  
.q-page{
  min-height: 100vh;
}
/* Align notifications + contact properly */
.row.justify-between {
  align-items: flex-start !important;
}

/* Aanbiedingen now anchored to bottom */
#aanbiedingen-section {
  bottom: 0px;   
}

.aanbieding-card {
  transition: transform 0.2s ease;
}

.aanbieding-card:hover {
  transform: translateY(-3px);
}

.text-primary {
  color: #1565c0;
}

.q-separator {
  margin: 0px;
  padding: 0px;
  margin-top: 50px;
}
</style>
