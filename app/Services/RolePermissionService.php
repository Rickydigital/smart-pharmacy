<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionService
{
    public function list(): array
    {
        return [
            'roles' => $this->roles(),
            'permissions' => $this->groupedPermissions(),
        ];
    }

    public function show(Role $role): array
    {
        $this->guardAdminRole($role);

        $role->load('permissions');

        return [
            'role' => $role,
            'permissions' => $this->groupedPermissions(),
            'canEditPermissions' => $this->canEditPermissions($role),
        ];
    }

    public function updatePermissions(Role $role, array $permissions): Role
    {
        $this->guardAdminRole($role);

        if (! $this->canEditPermissions($role)) {
            abort(403, 'This role permissions cannot be edited.');
        }

        $role->syncPermissions($permissions);

        return $role->load('permissions');
    }

    public function roles()
    {
        return Role::query()
            ->where('name', '!=', 'Admin')
            ->with('permissions')
            ->orderByRaw("
                CASE
                    WHEN name = 'Owner' THEN 1
                    WHEN name = 'Pharmacist' THEN 2
                    WHEN name = 'Cashier' THEN 3
                    WHEN name = 'Storekeeper' THEN 4
                    ELSE 5
                END
            ")
            ->get();
    }

    public function groupedPermissions()
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission) {
                return str($permission->name)->before('.')->toString();
            });
    }

    public function canEditPermissions(Role $role): bool
    {
        return ! in_array($role->name, ['Admin', 'Owner'], true);
    }

    private function guardAdminRole(Role $role): void
    {
        if ($role->name === 'Admin') {
            abort(403, 'Admin role is protected.');
        }
    }
}