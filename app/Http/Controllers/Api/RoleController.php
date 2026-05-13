<?php

namespace App\Http\Controllers\Api;

use App\Services\RolePermissionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    public function __construct(private RolePermissionService $roles)
    {
    }

    public function index(): mixed
    {
        return $this->success($this->roles->list());
    }

    public function show(Role $role): mixed
    {
        return $this->success($this->roles->show($role));
    }

    public function updatePermissions(Request $request, Role $role): mixed
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = $this->roles->updatePermissions(
            $role,
            $validated['permissions'] ?? []
        );

        return $this->success($role, 'Role permissions updated successfully.');
    }
}