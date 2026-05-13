<?php

namespace App\Http\Controllers;

use App\Services\RolePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller implements HasMiddleware
{
    public function __construct(private RolePermissionService $roles)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:user.view', only: ['index', 'show']),
            new Middleware('permission:user.manage', only: ['updatePermissions']),
        ];
    }

    public function index(): View
    {
        $data = $this->roles->list();

        return view('roles.index', [
            'roles' => $data['roles'],
            'permissions' => $data['permissions'],
        ]);
    }

    public function show(Role $role): View
    {
        $data = $this->roles->show($role);

        return view('roles.show', [
            'role' => $data['role'],
            'permissions' => $data['permissions'],
            'canEditPermissions' => $data['canEditPermissions'],
        ]);
    }

    public function updatePermissions(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $this->roles->updatePermissions($role, $validated['permissions'] ?? []);

        return redirect()
            ->route('roles.show', $role)
            ->with('success', 'Role permissions updated successfully.');
    }
}