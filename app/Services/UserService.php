<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Pharmacy;
use App\Models\User;
use App\Notifications\EmployeeAccountCreatedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserService
{
    public function list(array $filters = [])
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $query = User::with(['branch', 'roles:id,name'])
            ->where('pharmacy_id', $pharmacy->id)
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Admin', 'Owner']);
            })
            ->orderBy('first_name')
            ->orderBy('last_name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($roleQuery) use ($filters) {
                $roleQuery->where('name', $filters['role']);
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $users = $query->paginate($filters['per_page'] ?? 15);

        $users->getCollection()->transform(function ($user) {
            $user->role = $user->roles->first()?->name ?? null;
            return $user;
        });

        return $users;
    }

    public function formData(): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        return [
            'branches' => Branch::query()
                ->where('pharmacy_id', $pharmacy->id)
                ->where('is_active', true)
                ->orderByDesc('is_main')
                ->orderBy('name')
                ->get(),

            'roles' => Role::query()
                ->whereNotIn('name', ['Admin', 'Owner'])
                ->orderBy('name')
                ->get(),
        ];
    }

    public function create(array $data): User
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        $username = $this->generateUsername($data['first_name'], $data['last_name'] ?? null);
        $plainPassword = $this->generatePassword();

        $user = User::query()->create([
            'pharmacy_id' => $pharmacy->id,
            'branch_id' => $data['branch_id'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'username' => $username,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $plainPassword,
            'status' => $data['status'],
        ]);

        $user->syncRoles([$data['role']]);

        $user->unsetRelation('roles');

        $user->notify(new EmployeeAccountCreatedNotification($username, $plainPassword));

        return $user->fresh(['branch', 'roles']);
    }

    public function update(User $user, array $data): User
    {
        $this->guardProtectedUser($user);
        $this->guardSamePharmacy($user);

        $user->update([
            'branch_id' => $data['branch_id'] ?? null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        $user->syncRoles([$data['role']]);

        $user->unsetRelation('roles');

        return $user->fresh(['branch', 'roles']);
    }

    public function resetPassword(User $user): void
    {
        $this->guardProtectedUser($user);
        $this->guardSamePharmacy($user);

        if (!$user->email) {
            throw new \Exception('This user does not have an email address for password notification.');
        }

        $plainPassword = $this->generatePassword();

        $user->update([
            'password' => $plainPassword,
        ]);

        $user->notify(new EmployeeAccountCreatedNotification($user->username, $plainPassword));
    }

    public function toggle(User $user): User
    {
        $this->guardProtectedUser($user);
        $this->guardSamePharmacy($user);

        if ((int) $user->id === (int) Auth::id()) {
            throw new \Exception('You cannot deactivate your own account.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        return $user->refresh();
    }

    public function allowedRoleNames(): array
    {
        return Role::query()
            ->whereNotIn('name', ['Admin', 'Owner'])
            ->pluck('name')
            ->toArray();
    }

    private function generateUsername(string $firstName, ?string $lastName = null): string
    {
        $base = Str::slug(strtolower($firstName . ($lastName ? '.' . $lastName : '')), '.');
        $base = trim($base, '.') ?: 'user';

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

    private function guardSamePharmacy(User $user): void
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        if ((int) $user->pharmacy_id !== (int) $pharmacy->id) {
            abort(403);
        }
    }
}
