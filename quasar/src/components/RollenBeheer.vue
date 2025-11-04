<template>
  <q-dialog v-model="localDialog" persistent>
    <q-card style="min-width: 1100px; max-height: 90vh;" class="scroll">
      <!-- Title -->
      <q-card-section class="row items-center justify-between">
        <div class="text-h6 text-primary">Rollenbeheer</div>
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <!-- Table of Roles (now draggable) -->
      <q-card-section class="relative-position">
        <!-- 🔹 Loading overlay -->
        <q-inner-loading :showing="loading">
          <q-spinner color="primary" size="40px" />
          <div class="text-grey-7 q-mt-sm">Rollen en permissies laden...</div>
        </q-inner-loading>

        <draggable
          v-if="!loading"
          v-model="roles"
          item-key="id"
          handle=".drag-handle"
          @end="updateRoleOrder"
        >
          <template #item="{ element }">
            <q-item dense bordered class="q-mb-xs bg-grey-1">
              <q-item-section avatar>
                <q-icon name="drag_indicator" class="drag-handle cursor-pointer" color="grey-6" />
              </q-item-section>
              <q-item-section>
                <div class="text-subtitle2">{{ element.name }}</div>
              </q-item-section>
              <q-item-section side>
                <q-btn dense round flat color="primary" icon="edit" @click="editRole(element)" />
                <q-btn dense round flat color="negative" icon="delete" @click="confirmDelete(element)" />
              </q-item-section>
            </q-item>
          </template>
        </draggable>

        <div v-if="!loading" class="text-right q-mt-md">
          <q-btn color="primary" icon="add" label="Nieuwe rol" @click="openAddDialog" :loading="createLoading" />
        </div>
      </q-card-section>

      <!-- Add/Edit Role Dialog -->
      <q-dialog v-model="editDialog">
        <q-card style="min-width: 800px; max-height: 90vh;" class="scroll">
          <q-card-section>
            <div class="text-h6">{{ editMode ? 'Rol bewerken' : 'Nieuwe rol' }}</div>
          </q-card-section>

          <q-card-section class="q-gutter-md">
            <q-input v-model="form.name" label="Rolnaam" outlined dense />
            <q-input
              v-model.number="form.roleOrder"
              label="Prioriteit"
              outlined
              dense
              type="number"
            />

            <!-- Permissions grouped by category -->
            <div
              v-for="(group, category) in groupedPermissions"
              :key="category"
              class="q-mt-md q-pb-sm q-border-b"
            >
              <div class="row items-center q-mb-sm">
                <div class="text-subtitle2 text-primary q-mr-sm">{{ category }}</div>
                <q-checkbox
                  :model-value="isCategoryFullyChecked(category)"
                  :indeterminate="isCategoryPartiallyChecked(category)"
                  @update:model-value="toggleCategory(category, $event)"
                  dense
                  color="primary"
                  class="q-ml-sm"
                />
              </div>

              <!-- Permissions grid -->
              <div class="permissions-grid q-gutter-sm">
                <div
                  v-for="perm in group"
                  :key="perm.id"
                  class="permission-item"
                >
                  <q-checkbox
                    :model-value="Array.isArray(form.permissions) && form.permissions.includes(perm.name)"
                    @update:model-value="togglePermission(perm.name, $event)"
                    :label="perm.description"
                    dense
                  />
                </div>
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Annuleren" v-close-popup />
            <q-btn color="primary" :label="editMode ? 'Opslaan' : 'Toevoegen'" @click="saveRole" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-card>
  </q-dialog>
</template>

<script>
import draggable from 'vuedraggable'
import { get, post, put, del } from '../../../resources/js/bootstrap.js'

export default {
  name: 'RollenBeheer',
  components: { draggable },
  props: {
    modelValue: { type: Boolean, required: true }
  },
  emits: ['update:modelValue'],

  data() {
    return {
      roles: [],
      permissions: [],
      loading: true,
      createLoading: false,
      editDialog: false,
      editMode: false,
      form: {
        id: null,
        name: '',
        roleOrder: 1,
        permissions: []
      }
    }
  },

  computed: {
    localDialog: {
      get() {
        return this.modelValue
      },
      set(val) {
        this.$emit('update:modelValue', val)
      }
    },
    groupedPermissions() {
      const groups = {}
      this.permissions.forEach(p => {
        if (!groups[p.category]) groups[p.category] = []
        groups[p.category].push(p)
      })
      return groups
    }
  },

  methods: {
    async loadRolesAndPermissions() {
      this.loading = true
      try {
        const [rolesRes, permRes] = await Promise.all([get('roles'), get('roles/permissions')])

        this.roles = rolesRes.data.map(role => ({
          ...role,
          permissions: Array.isArray(role.permissions)
            ? role.permissions.map(p => (typeof p === 'object' ? p.name : p))
            : []
        }))
        this.permissions = permRes.data
      } catch (err) {
        console.error('Fout bij laden rollen of permissies', err)
      } finally {
        this.loading = false
      }
    },

    openAddDialog() {
      const nextOrder =
        this.roles.length > 0
          ? Math.max(...this.roles.map(r => r.roleOrder || 0)) + 1
          : 1

      this.form = { id: null, name: '', roleOrder: nextOrder, permissions: [] }
      this.editMode = false
      this.editDialog = true
    },

    editRole(role) {
      this.form = {
        id: role.id,
        name: role.name,
        roleOrder: role.roleOrder,
        permissions: Array.isArray(role.permissions)
          ? role.permissions.map(p => (typeof p === 'object' ? p.name : p))
          : []
      }
      this.editMode = true
      this.editDialog = true
    },

    togglePermission(permissionName, value) {
      const index = this.form.permissions.indexOf(permissionName)
      if (value && index === -1) this.form.permissions.push(permissionName)
      else if (!value && index !== -1) this.form.permissions.splice(index, 1)
    },

    isCategoryFullyChecked(category) {
      const group = this.groupedPermissions[category] || []
      return group.length > 0 && group.every(p => this.form.permissions.includes(p.name))
    },

    isCategoryPartiallyChecked(category) {
      const group = this.groupedPermissions[category] || []
      const checkedCount = group.filter(p => this.form.permissions.includes(p.name)).length
      return checkedCount > 0 && checkedCount < group.length
    },

    toggleCategory(category, value) {
      const group = this.groupedPermissions[category] || []
      if (value) {
        group.forEach(p => {
          if (!this.form.permissions.includes(p.name)) this.form.permissions.push(p.name)
        })
      } else {
        this.form.permissions = this.form.permissions.filter(
          name => !group.some(p => p.name === name)
        )
      }
    },

    async saveRole() {
      this.createLoading = true
      const payload = {
        name: this.form.name,
        idCompany: 1,
        roleOrder: this.form.roleOrder,
        permissions: this.form.permissions,
        readTicketsOfRoles: [],
        getNotificationsOf: []
      }

      try {
        let res
        if (this.editMode) {
          res = await put(`roles/${this.form.id}`, payload)
          const index = this.roles.findIndex(r => r.id === this.form.id)
          if (index !== -1) this.roles.splice(index, 1, res.data)
        } else {
          res = await post('roles', payload)
          this.roles.push(res.data)
        }
        this.editDialog = false
      } catch (err) {
        console.error('Fout bij opslaan rol', err)
      } finally {
        this.createLoading = false
      }
    },

    confirmDelete(role) {
      if (confirm(`Weet je zeker dat je de rol "${role.name}" wilt verwijderen?`)) {
        del(`roles/${role.id}`)
          .then(() => (this.roles = this.roles.filter(r => r.id !== role.id)))
          .catch(err => console.error('Fout bij verwijderen rol', err))
      }
    },

    async updateRoleOrder() {
      try {
        for (let i = 0; i < this.roles.length; i++) {
          const role = this.roles[i]
          await put(`roles/${role.id}`, { roleOrder: i + 1 })
          role.roleOrder = i + 1
        }
      } catch (err) {
        console.error('Fout bij bijwerken volgorde', err)
      }
    }
  },

  mounted() {
    this.loadRolesAndPermissions()
  }
}
</script>

<style scoped>
.role-table {
  table-layout: fixed;
  width: 100%;
}

.q-card{
  min-height: 120px;
}

.q-dialog__inner {
  overflow-y: auto !important;
}

.text-primary {
  color: #1565c0;
}

.q-border-b {
  border-bottom: 1px solid #eee;
}

/* 🔹 Standardized checkbox grid layout */
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
