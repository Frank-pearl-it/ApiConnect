import { defineStore } from 'pinia'
import { get, post, put, del } from '../../../resources/js/bootstrap'

export const useRollenBeheerStore = defineStore('rollenBeheerStore', {
  state: () => ({
    roles: [],
    permissions: [],
    loading: false,
    saving: false,
    error: null
  }),

  getters: {
    groupedPermissions(state) {
      const groups = {}
      state.permissions.forEach(p => {
        if (!groups[p.category]) groups[p.category] = []
        groups[p.category].push(p)
      })
      return groups
    }
  },

  actions: {
    /* ---------- Load roles + permissions ---------- */
    async load() {
      this.loading = true
      this.error = null
      try {
        const [rolesRes, permRes] = await Promise.all([
          get('roles'),
          get('roles/permissions')
        ])

        this.roles = rolesRes.data.map(r => ({
          ...r,
          permissions: typeof r.permissions === 'object' ? r.permissions : {},
          readTicketsOfRoles: Array.isArray(r.readTicketsOfRoles) ? r.readTicketsOfRoles : [],
         }))

        this.permissions = permRes.data
      } catch (err) {
        console.error('Fout bij laden van rollen of permissies:', err)
        this.error = err
      } finally {
        this.loading = false
      }
    },

    /* ---------- Save role ---------- */
    async saveRole(role) {
      this.saving = true
      this.error = null
      try {
        const payload = { ...role }

        if (role.id) {
          const res = await put(`roles/${role.id}`, payload)
          const index = this.roles.findIndex(r => r.id === role.id)
          if (index !== -1) this.roles.splice(index, 1, res.data)
        } else {
          const res = await post('roles', payload)
          this.roles.push(res.data)
        }
      } catch (err) {
        console.error('Fout bij opslaan van rol:', err)
        this.error = err
      } finally {
        this.saving = false
      }
    },

    /* ---------- Delete role ---------- */
    async deleteRole(roleId) {
      this.error = null
      try {
        await del(`roles/${roleId}`)
        this.roles = this.roles.filter(r => r.id !== roleId)
      } catch (err) {
        console.error('Fout bij verwijderen van rol:', err)
        this.error = err
      }
    },

    /* ---------- Update order ---------- */
    async updateOrder() {
      this.error = null
      try {
        for (let i = 0; i < this.roles.length; i++) {
          const role = this.roles[i]
          await put(`roles/${role.id}`, { roleOrder: i + 1 })
          role.roleOrder = i + 1
        }
      } catch (err) {
        console.error('Fout bij bijwerken van rolvolgorde:', err)
        this.error = err
      }
    },

    /* ---------- Utility helpers ---------- */
    buildEmptyReadTicketsMap(excludeRoleId = null) {
      const map = {}
      this.roles.forEach(r => {
        if (r.id === excludeRoleId) return
        map[r.id] = { canView: false, canEdit: false, canClose: false, canReopen: false }
      })
      return map
    },
 

    createEmptyRoleTemplate() {
      const nextOrder = this.roles.length > 0
        ? Math.max(...this.roles.map(r => r.roleOrder || 0)) + 1
        : 1

      return {
        id: null,
        name: '',
        description: '',
        idCompany: 1,
        roleOrder: nextOrder,
        permissions: {},
        readTicketsOfRolesMap: this.buildEmptyReadTicketsMap(null), 
      }
    }
  }
})
