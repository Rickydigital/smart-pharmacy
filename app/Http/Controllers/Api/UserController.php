<?php

namespace App\Http\Controllers\Api;

use App\Models\Pharmacy;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends ApiController
{
    public function __construct(private UserService $users)
    {
    }

    public function index(Request $request): mixed
    {
        return $this->success([
            'users' => $this->users->list($request->only(['search', 'role', 'status', 'per_page'])),
            'form_data' => $this->users->formData(),
        ]);
    }

    public function store(Request $request): mixed
    {
        $validated = $this->validateUser($request);

        $user = $this->users->create($validated);

        return $this->success($user, 'Employee created successfully.');
    }

    public function update(Request $request, User $user): mixed
    {
        $validated = $this->validateUser($request, $user);

        $user = $this->users->update($user, $validated);

        return $this->success($user, 'Employee updated successfully.');
    }

    public function toggle(User $user): mixed
    {
        try {
            $user = $this->users->toggle($user);

            return $this->success($user, 'Employee status updated successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function resetPassword(User $user): mixed
    {
        try {
            $this->users->resetPassword($user);

            return $this->success(null, 'Password reset successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $pharmacy = Pharmacy::query()->firstOrFail();

        return $request->validate([
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where('pharmacy_id', $pharmacy->id),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['required', 'in:active,inactive,blocked'],
            'role' => ['required', Rule::in($this->users->allowedRoleNames())],
        ]);
    }
}