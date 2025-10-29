<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    /**
     * Get all roles for a company.
     */
    public function getAll(int $idCompany): Collection
    {
        return Role::where('idCompany', $idCompany)
            ->orderBy('roleOrder')
            ->get();
    }



    /**
     * Get all permissions.
     */
    public function getPermissions(): Collection
    {
        return Permission::orderBy('category')->get();
    }

    /**
     * Create a new role.
     */
    public function create(array $data): Role
    {
        return Role::create([
            'name' => $data['name'],
            'idCompany' => $data['idCompany'],
            'roleOrder' => $data['roleOrder'],
            'permissions' => $data['permissions'] ?? [],
            'readTicketsOfRoles' => $data['readTicketsOfRoles'] ?? [],
            'getNotificationsOf' => $data['getNotificationsOf'] ?? [],
        ]);
    }

    /**
     * Update a role.
     */
    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role;
    }

    /**
     * Delete a role.
     */
    public function delete(int $id): bool
    {
        $role = Role::findOrFail($id);
        return $role->delete();
    }
}
