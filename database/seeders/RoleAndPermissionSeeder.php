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
            ['id' => 1, 'name' => 'Beheerder', 'description' => 'Volledige toegang tot het systeem', 'roleOrder' => 1],
            ['id' => 2, 'name' => 'Manager', 'description' => 'Kan gebruikers, producten en tickets beheren', 'roleOrder' => 2],
            ['id' => 3, 'name' => 'Medewerker', 'description' => 'Kan eigen tickets beheren en producten bestellen', 'roleOrder' => 3],
            ['id' => 4, 'name' => 'Kijker', 'description' => 'Alleen leesrechten op relevante gegevens', 'roleOrder' => 4],
        ];

        // ---- PERMISSIONS ----
        $permissions = [
            // Gebruikers
            ['name' => 'viewUsers', 'description' => 'Mag gebruikers bekijken', 'category' => 'Gebruikers', 'default' => ['value' => false]],
            ['name' => 'editUsers', 'description' => 'Mag gebruikers bewerken', 'category' => 'Gebruikers', 'default' => ['value' => false]],
            ['name' => 'createUsers', 'description' => 'Mag gebruikers aanmaken', 'category' => 'Gebruikers', 'default' => ['value' => false]],
            ['name' => 'deleteUsers', 'description' => 'Mag gebruikers verwijderen', 'category' => 'Gebruikers', 'default' => ['value' => false]],

            // Rollen
            ['name' => 'viewRoles', 'description' => 'Mag rollen bekijken', 'category' => 'Rollen', 'default' => ['value' => false]],
            ['name' => 'editRoles', 'description' => 'Mag rollen bewerken', 'category' => 'Rollen', 'default' => ['value' => false]],
            ['name' => 'createRoles', 'description' => 'Mag rollen aanmaken', 'category' => 'Rollen', 'default' => ['value' => false]],
            ['name' => 'deleteRoles', 'description' => 'Mag rollen verwijderen', 'category' => 'Rollen', 'default' => ['value' => false]],

            // Tickets
            ['name' => 'viewTickets', 'description' => 'Mag tickets bekijken', 'category' => 'Tickets', 'default' => ['value' => false]],
            ['name' => 'editTickets', 'description' => 'Mag tickets bewerken', 'category' => 'Tickets', 'default' => ['value' => false]],
            ['name' => 'createTickets', 'description' => 'Mag tickets aanmaken', 'category' => 'Tickets', 'default' => ['value' => false]],
            ['name' => 'deleteTickets', 'description' => 'Mag tickets verwijderen', 'category' => 'Tickets', 'default' => ['value' => false]],
            ['name' => 'closeTickets', 'description' => 'Mag tickets sluiten', 'category' => 'Tickets', 'default' => ['value' => false]],
            ['name' => 'reopenTickets', 'description' => 'Mag gesloten tickets heropenen', 'category' => 'Tickets', 'default' => ['value' => false]],

            // Facturen
            ['name' => 'viewInvoices', 'description' => 'Mag facturen bekijken', 'category' => 'Facturen', 'default' => ['value' => false]],

            // Producten
            ['name' => 'viewProducts', 'description' => 'Mag producten bekijken', 'category' => 'Producten', 'default' => ['value' => false]],
            ['name' => 'orderProducts', 'description' => 'Mag producten bestellen', 'category' => 'Producten', 'default' => ['value' => false]],
            ['name' => 'orderPriceLimit', 'description' => 'Maximale bestelwaarde (in euro’s)', 'category' => 'Producten', 'default' => ['value' => 0.00]],

            // Systeem
            ['name' => 'viewLogs', 'description' => 'Mag systeemlogboeken bekijken', 'category' => 'Systeem', 'default' => ['value' => false]],
        ];

        // Insert or update permissions
        foreach ($permissions as $permData) {
            Permission::updateOrCreate(
                ['name' => $permData['name']],
                [
                    'description' => $permData['description'],
                    'default' => $permData['default'],
                    'category' => $permData['category'],
                ]
            );
        }

        // ---- ROLE DEFAULT PERMISSIONS ----
        $roleDefaults = [
            'Beheerder' => [
                'viewUsers' => true, 'editUsers' => true, 'createUsers' => true, 'deleteUsers' => true,
                'viewRoles' => true, 'editRoles' => true, 'createRoles' => true, 'deleteRoles' => true,
                'viewTickets' => true, 'editTickets' => true, 'createTickets' => true, 'deleteTickets' => true, 'closeTickets' => true, 'reopenTickets' => true,
                'viewInvoices' => true,
                'viewProducts' => true, 'orderProducts' => true, 'orderPriceLimit' => 99999.99,
                'viewLogs' => true,
            ],
            'Manager' => [
                'viewUsers' => true, 'editUsers' => true, 'createUsers' => true, 'deleteUsers' => false,
                'viewRoles' => true, 'editRoles' => false, 'createRoles' => false, 'deleteRoles' => false,
                'viewTickets' => true, 'editTickets' => true, 'createTickets' => true, 'deleteTickets' => false, 'closeTickets' => true, 'reopenTickets' => true,
                'viewInvoices' => true,
                'viewProducts' => true, 'orderProducts' => true, 'orderPriceLimit' => 500.00,
                'viewLogs' => false,
            ],
            'Medewerker' => [
                'viewTickets' => true, 'createTickets' => true, 'editTickets' => true, 'closeTickets' => true,
                'viewProducts' => true, 'orderProducts' => true, 'orderPriceLimit' => 100.00,
                'viewInvoices' => true,
            ],
            'Kijker' => [
                'viewTickets' => true, 'createTickets' => false, 'editTickets' => false,
                'viewProducts' => true, 'orderProducts' => false, 'orderPriceLimit' => 0.00,
                'viewInvoices' => true,
            ],
        ];

        // ---- READ TICKETS OF ROLES ----
        $readMap = [
            'Beheerder' => [
                ['idRole' => 2, 'canView' => true, 'canEdit' => true, 'canClose' => true, 'canReopen' => true],
                ['idRole' => 3, 'canView' => true, 'canEdit' => true, 'canClose' => true, 'canReopen' => true],
                ['idRole' => 4, 'canView' => true, 'canEdit' => true, 'canClose' => true, 'canReopen' => true],
            ],
            'Manager' => [
                ['idRole' => 3, 'canView' => true, 'canEdit' => true, 'canClose' => true, 'canReopen' => true],
                ['idRole' => 4, 'canView' => true, 'canEdit' => true, 'canClose' => true, 'canReopen' => true],
            ],
            'Medewerker' => [],
            'Kijker' => [],
        ];

        // ---- DEFAULT NOTIFICATIONS ----
        $notifications = [
            "ticketResponse" => ["self"],
            "ticketClosed" => ["self"],
            "ticketReopened" => ["self"],
            "ticketCreated" => ["self"],
            "productOrdered" => ["self"],
        ];

        // ---- CREATE / UPDATE ROLES ----
        foreach ($roles as $roleData) {
            $name = $roleData['name'];

            Role::updateOrCreate(
                ['name' => $name],
                [
                    'description' => $roleData['description'],
                    'roleOrder' => $roleData['roleOrder'],
                    'permissions' => $roleDefaults[$name] ?? [],
                    'readTicketsOfRoles' => $readMap[$name] ?? [], 
                ]
            );
        }
    }
}
