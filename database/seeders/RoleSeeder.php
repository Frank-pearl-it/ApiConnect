<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure permissions exist
        $permissions = Permission::pluck('name')->toArray();

        $roles = [
            [
                'name' => 'Admin',
                'idCompany' => 1,
                'roleOrder' => 1,
                'permissions' => $permissions, // all permissions
                'readTicketsOfRoles' => [],
                'getNotificationsOf' => [],
            ],
            [
                'name' => 'Manager',
                'idCompany' => 1,
                'roleOrder' => 2,
                'permissions' => ['view_user', 'edit_user'],
                'readTicketsOfRoles' => [],
                'getNotificationsOf' => [],
            ],
            [
                'name' => 'User',
                'idCompany' => 1,
                'roleOrder' => 3,
                'permissions' => ['view_user'],
                'readTicketsOfRoles' => [],
                'getNotificationsOf' => [],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}
