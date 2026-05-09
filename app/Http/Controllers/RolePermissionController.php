<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:user.view', only: ['index', 'show']),
            new Middleware('permission:user.manage', only: ['updatePermissions']),
        ];
    }

    public function index(): View
    {
        $roles = Role::query()
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

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission) {
                return str($permission->name)->before('.')->toString();
            });

        return view('roles.index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function show(Role $role): View
    {
        $this->guardAdminRole($role);

        $role->load('permissions');

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission) {
                return str($permission->name)->before('.')->toString();
            });

        return view('roles.show', [
            'role' => $role,
            'permissions' => $permissions,
            'canEditPermissions' => $this->canEditPermissions($role),
        ]);
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $this->guardAdminRole($role);

        if (! $this->canEditPermissions($role)) {
            abort(403, 'This role permissions cannot be edited.');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()
            ->route('roles.show', $role)
            ->with('success', 'Role permissions updated successfully.');
    }

    private function guardAdminRole(Role $role): void
    {
        if ($role->name === 'Admin') {
            abort(403, 'Admin role is protected.');
        }
    }

    private function canEditPermissions(Role $role): bool
    {
        return ! in_array($role->name, ['Admin', 'Owner'], true);
    }
}