<template>
  <q-page class="q-pa-xl bg-white">
    <div class="content-wrapper">
      <!-- Facturen Table -->
      <q-table
        title="Facturen"
        :rows="filteredInvoices"
        :columns="columns"
        row-key="id"
        flat
        bordered
        :pagination="{ rowsPerPage: 10 }"
        class="invoice-table"
      >
        <!-- Left side (Download overzicht button) -->
        <template #top-left>
          <q-btn
            color="primary"
            icon="description"
            label="Download overzicht"
            @click="downloadOverview"
            class="q-mr-sm"
          />
        </template>

        <!-- Right side (search + add button) -->
        <template #top-right>
          <div class="row items-center q-gutter-sm">
            <q-input
              dense
              outlined
              debounce="300"
              v-model="search"
              placeholder="Zoek factuur..."
              class="q-pr-sm"
              style="min-width: 220px"
            >
              <template #append>
                <q-icon name="search" />
              </template>
            </q-input>

            <q-btn
              color="primary"
              icon="add"
              label="Factuur toevoegen"
              @click="addInvoice"
            />
          </div>
        </template>

        <!-- Action buttons inside table -->
        <template #body-cell-actions="props">
          <q-td align="center" style="width: 120px;">
            <q-btn
              dense
              round
              flat
              color="primary"
              icon="visibility"
              @click="viewInvoice(props.row)"
            />
            <!-- <q-btn
              dense
              round
              flat
              color="negative"
              icon="delete"
              @click="deleteInvoice(props.row)"
            /> -->
          </q-td>
        </template>
      </q-table>
    </div>
  </q-page>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'FacturenPage',

  data() {
    return {
      search: '',
      invoices: [
        { id: 1, number: 'F2025-001', client: 'Tiwos BV', amount: '€ 2.300,00', status: 'Betaald', date: '12-02-2025' },
        { id: 2, number: 'F2025-002', client: 'Pearl IT', amount: '€ 1.150,00', status: 'Open', date: '15-02-2025' },
        { id: 3, number: 'F2025-003', client: 'GreenTech', amount: '€ 3.800,00', status: 'In behandeling', date: '20-02-2025' },
        { id: 4, number: 'F2025-004', client: 'SoftNet', amount: '€ 540,00', status: 'Betaald', date: '25-02-2025' }
      ],

      columns: [
        { name: 'number', label: 'Factuurnummer', field: 'number', align: 'left', sortable: true, style: 'width: 180px;' },
        { name: 'client', label: 'Klant', field: 'client', align: 'left', sortable: true, style: 'width: 220px;' },
        { name: 'amount', label: 'Bedrag', field: 'amount', align: 'right', sortable: true, style: 'width: 140px;' },
        { name: 'status', label: 'Status', field: 'status', align: 'left', sortable: true, style: 'width: 160px;' },
        { name: 'date', label: 'Datum', field: 'date', align: 'center', sortable: true, style: 'width: 140px;' },
        { name: 'actions', label: 'Acties', align: 'center', style: 'width: 120px;' }
      ]
    }
  },

  computed: {
    filteredInvoices() {
      if (!this.search) return this.invoices
      const term = this.search.toLowerCase()
      return this.invoices.filter(
        f =>
          f.number.toLowerCase().includes(term) ||
          f.client.toLowerCase().includes(term) ||
          f.amount.toLowerCase().includes(term) ||
          f.status.toLowerCase().includes(term) ||
          f.date.toLowerCase().includes(term)
      )
    }
  },

  methods: {
    downloadOverview() {
      console.log('Download overzicht aangeroepen')
    },
    addInvoice() {
      console.log('Nieuwe factuur toevoegen')
    },
    viewInvoice(invoice) {
      console.log('Factuur bekijken:', invoice)
    },
    deleteInvoice(invoice) {
      console.log('Factuur verwijderen:', invoice)
    }
  }
})
</script>

<style scoped lang="scss">
.content-wrapper {
  min-height: 80vh !important;
}

.invoice-table {
  table-layout: fixed !important; /* keeps cols stable */
  width: 100%;
}

.q-table th,
.q-table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.q-page {
  background: white;
}

.q-table {
  border-radius: 12px;
}
</style>
