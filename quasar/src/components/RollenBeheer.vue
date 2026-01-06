<template>
  <q-dialog v-model="localDialog" persistent>
    <q-card style="min-width: 1100px; max-height: 90vh;" class="scroll">
      <!-- Title -->
      <q-card-section class="row items-center justify-between">
        <div class="text-h6 text-primary">Rollen Beheer</div>
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <!-- Table of Roles -->
      <q-card-section class="relative-position">
        <q-inner-loading :showing="loading">
          <q-spinner color="primary" size="40px" />
          <div class="text-grey-7 q-mt-sm">Rollen en permissies laden...</div>
        </q-inner-loading>

        <draggable v-if="!loading" v-model="roles" item-key="id" handle=".drag-handle" @end="updateRoleOrder">
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
          <q-btn color="primary" icon="add" label="Nieuwe rol" @click="openAddDialog" :loading="createLoading" class="roles-button" />
        </div>
      </q-card-section>

      <!-- Add/Edit Role Dialog -->
      <q-dialog v-model="editDialog">
        <q-card style="min-width: 800px; max-height: 90vh;" class="scroll">
          <q-card-section>
            <div class="text-h6">{{ editMode ? 'Rol bewerken' : 'Nieuwe rol' }}</div>
          </q-card-section>

          <q-card-section class="q-gutter-md">
            <q-input v-model="form.name" label="Rolnaam" outlined dense class="rolename" />
            <q-input v-model.number="form.roleOrder" label="Prioriteit" outlined dense type="number" />
            <q-input v-model="form.description" label="Omschrijving" outlined dense type="text" class="role-description"/>
            <!-- Use subcomponents -->
            <RolePermissions v-model="form" />
            <RoleTicketAccess v-if="canEditAdvancedSections" v-model="form" /> 
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Annuleren" v-close-popup />
            <q-btn color="primary" class="save-role" :label="editMode ? 'Opslaan' : 'Toevoegen'" @click="saveRole" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </q-card>
  </q-dialog>
</template>

<script>
import draggable from 'vuedraggable'
import { get, post, put, del } from '../../../resources/js/bootstrap.js'
import { useRollenBeheerStore } from 'src/stores/useRollenBeheerStore.js'

// Subcomponents
import RolePermissions from './RollenBeheer/RolePermissions.vue' 
import RoleTicketAccess from './RollenBeheer/RoleTicketAccess.vue'
import { popup } from 'src/boot/custom-mixin.js'

export default {
  name: 'RollenBeheer',
  components: {
    draggable,
    RolePermissions,
    RoleTicketAccess, 
  },
  props: { modelValue: { type: Boolean, required: true } },
  emits: ['update:modelValue'],

  data() {
    return {
      store: useRollenBeheerStore(),
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
        permissions: {},
        readTicketsOfRolesMap: {}, 
      }
    }
  },

  computed: {
    localDialog: {
      get() { return this.modelValue },
      set(val) { this.$emit('update:modelValue', val) }
    },
    canEditAdvancedSections() {
      return true
    }
  },

  methods: {
    /* ---------- Helpers ---------- */
    buildEmptyReadTicketsMap(excludeRoleId = null) {
      const map = {}
      this.roles.forEach(r => {
        if (r.id === excludeRoleId) return
        map[r.id] = { canView: false, canEdit: false, canClose: false, canReopen: false }
      })
      return map
    }, 

    mergeReadTicketsMap(baseMap, arrayFromApi = []) {
      arrayFromApi.forEach(entry => {
        if (baseMap[entry.roleId]) {
          baseMap[entry.roleId] = {
            ...baseMap[entry.roleId],
            canView: !!entry.canView,
            canEdit: !!entry.canEdit,
            canClose: !!entry.canClose,
            canReopen: !!entry.canReopen
          }
        }
      })
      return baseMap
    }, 

    async loadRolesAndPermissions() {
      this.loading = true
      try {
        await this.store.load()
        this.roles = this.store.roles
        this.permissions = this.store.permissions
      } catch (err) {
        console.error('Fout bij laden rollen of permissies', err)
      } finally {
        this.loading = false
      }
    },


    /* ---------- Open dialogs ---------- */
    openAddDialog() {
      const nextOrder = this.roles.length
        ? Math.max(...this.roles.map(r => r.roleOrder || 0)) + 1
        : 1

      this.form = {
        id: null,
        name: '',
        roleOrder: nextOrder,
        permissions: {},
        readTicketsOfRolesMap: this.buildEmptyReadTicketsMap(null), 
      }

      this.editMode = false
      this.editDialog = true
    },

    editRole(role) {
      const baseMap = this.buildEmptyReadTicketsMap(role.id)
      const readTicketsOfRolesMap = this.mergeReadTicketsMap(baseMap, role.readTicketsOfRoles) 

      this.form = {
        id: role.id,
        name: role.name,
        roleOrder: role.roleOrder,
        permissions: { ...role.permissions },
        readTicketsOfRolesMap, 
      }

      this.editMode = true
      this.editDialog = true
    },

    /* ---------- Save / Delete / Order ---------- */
    async saveRole() {
      this.createLoading = true

      const readTicketsArray = Object.entries(this.form.readTicketsOfRolesMap).map(([roleId, perms]) => ({
        roleId: Number(roleId),
        canView: !!perms.canView,
        canEdit: !!perms.canEdit,
        canClose: !!perms.canClose,
        canReopen: !!perms.canReopen
      }))

      const payload = {
        name: this.form.name,
        idCompany: 1,
        description: this.form.description,
        roleOrder: this.form.roleOrder,
        permissions: this.form.permissions,
        readTicketsOfRoles: readTicketsArray, 
      }

      try {
        let res
        if (this.editMode) {
          res = await put(`roles/${this.form.id}`, payload)
          const index = this.roles.findIndex(r => r.id === this.form.id)
          if (index !== -1) this.roles.splice(index, 1, res.data)
          popup('Rol succesvol bijgewerkt.', 200)
        } else {
          res = await post('roles', payload)
          this.roles.push(res.data)
          popup(res.data.message, 200)
        }
        this.editDialog = false
      } catch (err) {
        console.error('Fout bij opslaan rol', err)
        popup(err.response)
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
.text-primary {
  color: #1565c0;
}

.bg-grey-1 {
  background-color: #f9f9f9;
}

.q-border-b {
  border-bottom: 1px solid #eee;
}
</style>
