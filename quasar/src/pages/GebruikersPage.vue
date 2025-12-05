<template>
    <RollenBeheer v-model="showRolesDialog" />
    <q-page class="q-pa-xl bg-white">
        <div class="content-wrapper">
            <!-- User Table -->
            <q-table title="Gebruikers" :rows="filteredUsers" :columns="columns" row-key="id" flat bordered
                :pagination="{ rowsPerPage: 10 }" class="user-table">
                <!-- Left side (Rollen Beheer button) -->
                <template #top-left>
                    <q-btn color="primary" icon="admin_panel_settings" label="Rollen Beheer" @click="showRolesDialog = true"
                        class="q-mr-sm" />
                </template>

                <!-- Right side (search + add button) -->
                <template #top-right>
                    <div class="row items-center q-gutter-sm">
                        <q-input dense outlined debounce="300" v-model="search" placeholder="Zoek gebruiker..."
                            class="q-pr-sm" style="min-width: 220px">
                            <template #append>
                                <q-icon name="search" />
                            </template>
                        </q-input>

                        <q-btn color="primary" icon="person_add" label="Gebruiker toevoegen" @click="addUser" />
                    </div>
                </template>

                <!-- Action buttons inside table -->
                <template #body-cell-actions="props">
                    <q-td align="center" style="width: 120px;">
                        <q-btn dense round flat color="primary" icon="edit" @click="editUser(props.row)" />
                        <q-btn dense round flat color="negative" icon="delete" @click="deleteUser(props.row)" />
                    </q-td>
                </template>
            </q-table>
        </div>
    </q-page>
</template>
<script>
import RollenBeheer from 'src/components/RollenBeheer.vue';
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'GebruikersPage',
    components: {
        RollenBeheer
    },
    data() {
        return {
            search: '',
            users: [ 
            ],
            columns: [
                { name: 'name', label: 'Naam', field: 'name', align: 'left', sortable: true, style: 'width: 180px;' },
                { name: 'email', label: 'E-mail', field: 'email', align: 'left', sortable: true, style: 'width: 260px;' },
                { name: 'role', label: 'Rol', field: 'role', align: 'left', sortable: true, style: 'width: 140px;' },
                { name: 'actions', label: 'Acties', align: 'center', style: 'width: 120px;' }
            ],
            showRolesDialog: false,
        }
    },

    computed: {
        filteredUsers() {
            if (!this.search) return this.users
            const term = this.search.toLowerCase()
            return this.users.filter(
                u =>
                    u.name.toLowerCase().includes(term) ||
                    u.email.toLowerCase().includes(term) ||
                    u.role.toLowerCase().includes(term)
            )
        }
    },

    methods: {
        openRoles() {
            console.log('Rollen beheer geopend')
        },
        addUser() {
            console.log('Nieuwe gebruiker toevoegen')
        },
        editUser(user) {
            console.log('Gebruiker bewerken:', user)
        },
        deleteUser(user) {
            console.log('Gebruiker verwijderen:', user)
        }
    }
})
</script>

<style scoped lang="scss">
.content-wrapper {
    min-height: 80vh !important;
}

.user-table {
    table-layout: fixed !important;
    /* prevents column shifting */
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
