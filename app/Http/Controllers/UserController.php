<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\User;
use App\Notifications\EmployeeAccountCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:user.view', only: ['index']),
            new Middleware('permission:user.manage', except: ['index']),
        ];
    }


    public function index(Request $request): View
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $query = User::with(['branch', 'roles'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Admin', 'Owner']);
            })
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($roleQuery) use ($request) {
                $roleQuery->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(15);

        $branches = Branch::query()
            ->where('pharmacy_id', $pharmacy->id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $roles = Role::query()
            ->whereNotIn('name', ['Admin', 'Owner'])
            ->orderBy('name')
            ->get();

        return view('users.index', compact('users', 'branches', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $allowedRoleNames = Role::query()
            ->whereNotIn('name', ['Admin', 'Owner'])
            ->pluck('name')
            ->toArray();

        $validated = $request->validate([
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive,blocked'],
            'role' => ['required', Rule::in($allowedRoleNames)],
        ]);

        $username = $this->generateUsername($validated['first_name'], $validated['last_name'] ?? null);
        $plainPassword = $this->generatePassword();

        $user = User::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'username' => $username,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $plainPassword,
            'status' => $validated['status'],
        ]);

        $user->syncRoles([$validated['role']]);

        $user->notify(new EmployeeAccountCreatedNotification($username, $plainPassword));

        return back()->with('success', 'Employee created successfully. Login details were sent to the employee email.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->guardProtectedUser($user);

        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $user->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }

        $allowedRoleNames = Role::query()
            ->whereNotIn('name', ['Admin', 'Owner'])
            ->pluck('name')
            ->toArray();

        $validated = $request->validate([
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive,blocked'],
            'role' => ['required', Rule::in($allowedRoleNames)],
        ]);

        $user->update([
            'branch_id' => $validated['branch_id'] ?? null,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', 'Employee updated successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $this->guardProtectedUser($user);

        if (! $user->email) {
            return back()->with('error', 'This user does not have an email address for password notification.');
        }

        $plainPassword = $this->generatePassword();

        $user->update([
            'password' => $plainPassword,
        ]);

        $user->notify(new EmployeeAccountCreatedNotification($user->username, $plainPassword));

        return back()->with('success', 'Password reset successfully. New login details were sent to employee email.');
    }

    public function toggle(User $user): RedirectResponse
    {
        $this->guardProtectedUser($user);

        if ((int) $user->id === (int) Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Employee status updated successfully.');
    }

    private function generateUsername(string $firstName, ?string $lastName = null): string
    {
        $base = Str::slug(
            strtolower($firstName . ($lastName ? '.' . $lastName : '')),
            '.'
        );

        $base = trim($base, '.');

        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }

    private function generatePassword(): string
    {
        return Str::password(
            length: 10,
            letters: true,
            numbers: true,
            symbols: false,
            spaces: false
        );
    }

    private function guardProtectedUser(User $user): void
    {
        if ($user->hasAnyRole(['Admin', 'Owner'])) {
            abort(403, 'This user cannot be managed from employee user management.');
        }
    }
}
