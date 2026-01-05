<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Role;
class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Get all roles for the authenticated company.
     */
    public function index(Request $request): JsonResponse
    {
        $idCompany = $request->user()->idCompany ?? $request->get('idCompany');
        return response()->json($this->roleService->getAll($idCompany));
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }


    /**
     * Get all available permissions.
     */
    public function permissions(): JsonResponse
    {
        return response()->json($this->roleService->getPermissions());
    }

    /**
     * Store a new role.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'idCompany' => 'required|integer',
            'roleOrder' => 'required|integer',
            'permissions' => 'array|required',
            'readTicketsOfRoles' => 'array|nullable', 
        ]);

        $role = $this->roleService->create($validated);
        return response()->json($role, 201);
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'roleOrder' => 'sometimes|integer',
            'permissions' => 'array|nullable',
            'readTicketsOfRoles' => 'array|nullable', 
        ]);

        $role = $this->roleService->update($id, $validated);
        return response()->json($role);
    }

    /**
     * Delete a role.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->roleService->delete($id);
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
