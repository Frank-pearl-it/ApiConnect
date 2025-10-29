<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'create_user',
                'description' => 'Mag nieuwe gebruikers aanmaken',
                'category' => 'Gebruikers',
                'default' => false,
            ],
            [
                'name' => 'edit_user',
                'description' => 'Mag bestaande gebruikers bewerken',
                'category' => 'Gebruikers',
                'default' => false,
            ],
            [
                'name' => 'remove_user',
                'description' => 'Mag gebruikers verwijderen',
                'category' => 'Gebruikers',
                'default' => false,
            ],
            [
                'name' => 'view_user',
                'description' => 'Mag gebruikers bekijken',
                'category' => 'Gebruikers',
                'default' => false,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
}
