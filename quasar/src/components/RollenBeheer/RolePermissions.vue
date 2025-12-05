<template>
  <q-expansion-item
    icon="lock"
    label="Permissies"
    caption="Beheer de rechten voor deze rol"
    expand-separator
    default-closed
  >
    <div v-if="store.loading" class="q-pa-md text-grey">
      <q-spinner size="24px" color="primary" /> Permissies laden...
    </div>

    <div v-else>
      <div
        v-for="(group, category) in store.groupedPermissions"
        :key="category"
        class="q-mt-md q-pb-sm q-border-b"
      >
        <!-- Category Header -->
        <div class="row items-center q-mb-sm">
          <div class="text-subtitle2 text-primary q-mr-sm">{{ category }}</div>
          <q-checkbox
            :model-value="isCategoryFullyChecked(category)"
            :indeterminate="isCategoryPartiallyChecked(category)"
            @update:model-value="val => toggleCategory(category, val)"
            dense color="primary"
          />
        </div>

        <!-- Permissions Grid -->
        <div class="permissions-grid q-gutter-sm">
          <div
            v-for="perm in group"
            :key="perm.id"
            class="permission-item"
          >
            <!-- Boolean permissions -->
            <template v-if="typeof perm.default?.value === 'boolean'">
              <q-checkbox
                :model-value="!!model.permissions[perm.name]"
                @update:model-value="val => updatePermission(perm.name, val)"
                :label="perm.description"
                dense
              />
            </template>

            <!-- Numeric permissions (like price limit) -->
            <template v-else>
              <q-input
                type="number"
                outlined dense
                :label="perm.description"
                :model-value="model.permissions[perm.name] ?? perm.default?.value ?? 0"
                @update:model-value="val => updatePermission(perm.name, Number(val))"
                style="min-width: 220px;"
              />
            </template>
          </div>
        </div>
      </div>
    </div>
  </q-expansion-item>
</template>

<script>
import { useRollenBeheerStore } from 'src/stores/useRollenBeheerStore.js'

export default {
  name: 'RolePermissions',

  props: {
    modelValue: { type: Object, required: true } // expects the role form object
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
    isCategoryFullyChecked(category) {
      const group = this.store.groupedPermissions[category] || []
      return group.length > 0 && group.every(p => !!this.model.permissions[p.name])
    },

    isCategoryPartiallyChecked(category) {
      const group = this.store.groupedPermissions[category] || []
      const checked = group.filter(p => !!this.model.permissions[p.name])
      return checked.length > 0 && checked.length < group.length
    },

    toggleCategory(category, value) {
      const group = this.store.groupedPermissions[category] || []
      group.forEach(p => {
        if (typeof p.default?.value === 'boolean') {
          this.model.permissions[p.name] = value
        }
      })
      this.$emit('update:modelValue', this.model)
    },

    /* ---------- Individual toggle ---------- */
    updatePermission(name, value) {
      this.model.permissions[name] = value
      this.$emit('update:modelValue', this.model)
    }
  }
}
</script>

<style scoped>
.q-border-b {
  border-bottom: 1px solid #eee;
}

.permissions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 6px 14px;
  align-items: start;
}

.permission-item {
  display: flex;
  align-items: center;
  padding: 4px 0;
}
</style>
