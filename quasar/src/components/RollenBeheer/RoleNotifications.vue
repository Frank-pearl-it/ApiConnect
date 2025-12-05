<template>
  <q-expansion-item
    icon="notifications"
    label="Meldingen ontvangen van"
    caption="Selecteer gebeurtenissen waarvan deze rol meldingen ontvangt"
    expand-separator
  >
    <div v-if="store.loading" class="q-pa-md text-grey">
      <q-spinner size="24px" color="primary" /> Meldingen laden...
    </div>

    <div v-else class="q-pa-sm">
      <div
        v-for="(rolesArr, eventType) in model.getNotificationsOf"
        :key="eventType"
        class="q-mb-md bg-grey-1 q-pa-sm rounded-borders"
      >
        <!-- Header row -->
        <div class="row items-center q-mb-xs">
          <div class="text-subtitle2 text-primary q-mr-sm">{{ eventType }}</div>
          <q-checkbox
            :model-value="isEventFullyChecked(eventType)"
            :indeterminate="isEventPartiallyChecked(eventType)"
            @update:model-value="val => toggleEventAll(eventType, val)"
            dense color="primary"
          />
        </div>

        <!-- Roles selection -->
        <div class="row q-gutter-sm">
          <!-- Current role -->
          <q-checkbox
            :label="currentRoleName"
            :model-value="rolesArr.includes(model.id)"
            @update:model-value="val => toggleNotification(eventType, model.id, val)"
            dense
          />

          <!-- Self user -->
          <q-checkbox
            label="Eigen gebruiker"
            :model-value="rolesArr.includes('selfUser')"
            @update:model-value="val => toggleNotification(eventType, 'selfUser', val)"
            dense
          />

          <!-- Other roles -->
          <q-checkbox
            v-for="r in store.roles.filter(rr => rr.id !== model.id)"
            :key="r.id"
            :label="r.name"
            :model-value="rolesArr.includes(r.id)"
            @update:model-value="val => toggleNotification(eventType, r.id, val)"
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
  name: 'RoleNotifications',
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
    },
    currentRoleName() {
      const role = this.store.roles.find(r => r.id === this.model.id)
      return role ? role.name : 'Eigen rol'
    }
  },

  methods: {
    /* ---------- Check helpers ---------- */
    isEventFullyChecked(eventType) {
      const arr = this.model.getNotificationsOf[eventType] || []
      const allIds = [
        this.model.id,
        'selfUser',
        ...this.store.roles.filter(r => r.id !== this.model.id).map(r => r.id)
      ]
      return allIds.every(id => arr.includes(id))
    },

    isEventPartiallyChecked(eventType) {
      const arr = this.model.getNotificationsOf[eventType] || []
      const allIds = [
        this.model.id,
        'selfUser',
        ...this.store.roles.filter(r => r.id !== this.model.id).map(r => r.id)
      ]
      const checked = arr.filter(id => allIds.includes(id))
      return checked.length > 0 && checked.length < allIds.length
    },

    /* ---------- Toggle helpers ---------- */
    toggleEventAll(eventType, val) {
      const arr = new Set(this.model.getNotificationsOf[eventType] || [])
      const allIds = [
        this.model.id,
        'selfUser',
        ...this.store.roles.filter(r => r.id !== this.model.id).map(r => r.id)
      ]

      if (val) {
        allIds.forEach(id => arr.add(id))
      } else {
        allIds.forEach(id => arr.delete(id))
      }
      this.model.getNotificationsOf[eventType] = Array.from(arr)
      this.$emit('update:modelValue', this.model)
    },

    toggleNotification(eventType, targetId, enabled) {
      const arr = this.model.getNotificationsOf[eventType]
      const idx = arr.indexOf(targetId)
      if (enabled && idx === -1) arr.push(targetId)
      if (!enabled && idx !== -1) arr.splice(idx, 1)
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
