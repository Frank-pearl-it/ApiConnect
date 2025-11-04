<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // ---- ROLES ----
        $roles = [
            ['name' => 'admin', 'description' => 'Full system access (superuser)', 'roleOrder' => 1],
            ['name' => 'manager', 'description' => 'Can manage tickets, users, and products but not system-level settings', 'roleOrder' => 2],
            ['name' => 'employee', 'description' => 'Standard company employee; can create and manage their own tickets', 'roleOrder' => 3],
            ['name' => 'viewer', 'description' => 'Read-only access to relevant company data', 'roleOrder' => 4],
        ];

        // ---- PERMISSIONS ----
        $permissions = [
            ['name' => 'viewTickets', 'description' => 'View all support tickets', 'category' => 'Tickets', 'default' => true],
            ['name' => 'createTickets', 'description' => 'Create new support tickets', 'category' => 'Tickets', 'default' => true],
            ['name' => 'editTickets', 'description' => 'Edit support tickets', 'category' => 'Tickets', 'default' => false],
            ['name' => 'closeTickets', 'description' => 'Close or delete tickets', 'category' => 'Tickets', 'default' => false],

            ['name' => 'viewUsers', 'description' => 'View company users', 'category' => 'Users', 'default' => false],
            ['name' => 'createUsers', 'description' => 'Create new users', 'category' => 'Users', 'default' => false],
            ['name' => 'editUsers', 'description' => 'Edit user information', 'category' => 'Users', 'default' => false],
            ['name' => 'deleteUsers', 'description' => 'Delete company users', 'category' => 'Users', 'default' => false],

            ['name' => 'viewProducts', 'description' => 'View product list', 'category' => 'Products', 'default' => true],
            ['name' => 'orderProducts', 'description' => 'Order products', 'category' => 'Products', 'default' => true],

            ['name' => 'viewFacturen', 'description' => 'View company invoices', 'category' => 'Finance', 'default' => false],

            ['name' => 'viewLogs', 'description' => 'View and access detailed system logs', 'category' => 'System', 'default' => false],
        ];

        // insert or update permissions
        foreach ($permissions as $permData) {
            Permission::updateOrCreate(
                ['name' => $permData['name']],
                [
                    'description' => $permData['description'],
                    'default' => ['value' => $permData['default']],
                    'category' => $permData['category'],
                ]
            );
        }

        // ---- DEFAULT ROLE PERMISSIONS ----
        $roleDefaults = [
            'admin' => [
                'viewTickets' => true,
                'createTickets' => true,
                'editTickets' => true,
                'closeTickets' => true,
                'viewUsers' => true,
                'createUsers' => true,
                'editUsers' => true,
                'deleteUsers' => true,
                'viewProducts' => true,
                'orderProducts' => true,
                'viewFacturen' => true,
                'viewLogs' => true,
            ],
            'manager' => [
                'viewTickets' => true,
                'createTickets' => true,
                'editTickets' => true,
                'closeTickets' => true,
                'viewUsers' => true,
                'createUsers' => true,
                'editUsers' => true,
                'deleteUsers' => false,
                'viewProducts' => true,
                'orderProducts' => true,
                'viewFacturen' => true,
                'viewLogs' => false,
            ],
            'employee' => [
                'viewTickets' => true,
                'createTickets' => true,
                'editTickets' => true,
                'closeTickets' => false,
                'viewUsers' => false,
                'createUsers' => false,
                'editUsers' => false,
                'deleteUsers' => false,
                'viewProducts' => true,
                'orderProducts' => true,
                'viewFacturen' => true,
                'viewLogs' => false,
            ],
            'viewer' => [
                'viewTickets' => true,
                'createTickets' => false,
                'editTickets' => false,
                'closeTickets' => false,
                'viewUsers' => false,
                'createUsers' => false,
                'editUsers' => false,
                'deleteUsers' => false,
                'viewProducts' => true,
                'orderProducts' => false,
                'viewFacturen' => true,
                'viewLogs' => false,
            ],
        ];

        // create or update roles with JSON permissions
        foreach ($roles as $roleData) {
            $roleName = $roleData['name'];
            $role = Role::updateOrCreate(
                ['name' => $roleName],
                [
                    'description' => $roleData['description'],
                    'roleOrder' => $roleData['roleOrder'],
                    'permissions' => $roleDefaults[$roleName] ?? [],
                    'readTicketsOfRoles' => [],
                    'getNotificationsOf' => [],
                ]
            );
        }
    }
}
