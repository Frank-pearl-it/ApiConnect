<template>
  <q-expansion-item
    icon="visibility"
    label="Tickets van andere rollen"
    caption="Bepaal van welke rollen deze rol de tickets van kan bekijken of bewerken"
    expand-separator class="ticket-expand"
  >
    <div v-if="store.loading" class="q-pa-md text-grey">
      <q-spinner size="24px" color="primary" /> Rollen laden...
    </div>

    <div v-else class="q-pa-sm">
      <div
        v-for="targetRole in store.roles.filter(r => r.id !== model.id)"
        :key="targetRole.id"
        class="q-mb-sm bg-grey-1 q-pa-sm rounded-borders"
      >
        <!-- Role header + group toggle -->
        <div class="row items-center q-mb-xs ticket-header">
          <div class="text-subtitle2 text-primary q-mr-sm">{{ targetRole.name }}</div>
          <q-checkbox
            :model-value="isRoleFullyChecked(targetRole.id)"
            :indeterminate="isRolePartiallyChecked(targetRole.id)"
            @update:model-value="val => toggleAllForRole(targetRole.id, val)"
            dense color="primary"
          />
        </div>

        <!-- Permission toggles -->
        <div class="row q-gutter-md ticket-toggle">
          <q-checkbox
            v-model="model.readTicketsOfRolesMap[targetRole.id].canView"
            label="Bekijken"
            dense
          />
          <q-checkbox
            v-model="model.readTicketsOfRolesMap[targetRole.id].canEdit"
            label="Bewerken"
            dense
          />
          <q-checkbox
            v-model="model.readTicketsOfRolesMap[targetRole.id].canClose"
            label="Sluiten"
            dense
          />
          <q-checkbox
            v-model="model.readTicketsOfRolesMap[targetRole.id].canReopen"
            label="Heropenen"
            dense
          />
        </div>
      </div>
    </div>
  </q-expansion-item>
</template>

<script>
import { useRollenBeheerStore } from 'src/stores/useRollenBeheerStore.js'

export default {
  name: 'RoleTicketAccess',

  props: {
    modelValue: { type: Object, required: true } // expects the role form
  },

  emits: ['update:modelValue'],

  data() {
    return {
      store: useRollenBeheerStore()
    }
  },

  computed: {
    model: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      }
    }
  },

  methods: {
    /* ---------- Group toggles ---------- */
    isRoleFullyChecked(roleId) {
      const v = this.model.readTicketsOfRolesMap?.[roleId]
      return v && v.canView && v.canEdit && v.canClose && v.canReopen
    },

    isRolePartiallyChecked(roleId) {
      const v = this.model.readTicketsOfRolesMap?.[roleId]
      if (!v) return false
      const count = ['canView', 'canEdit', 'canClose', 'canReopen'].filter(k => v[k]).length
      return count > 0 && count < 4
    },

    toggleAllForRole(roleId, val) {
      const v = this.model.readTicketsOfRolesMap?.[roleId]
      if (!v) return
      v.canView = val
      v.canEdit = val
      v.canClose = val
      v.canReopen = val
      this.$emit('update:modelValue', this.model)
    }
  }
}
</script>

<style scoped>
.bg-grey-1 {
  background-color: #f9f9f9;
}
.rounded-borders {
  border-radius: 8px;
}
.text-primary {
  color: #1565c0;
}
</style>
