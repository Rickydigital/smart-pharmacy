<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller implements HasMiddleware
{
    public function __construct(private UserService $users)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:user.view', only: ['index']),
            new Middleware('permission:user.manage', except: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $users = $this->users->list($request->only(['search', 'role', 'status']));
        $formData = $this->users->formData();

        return view('users.index', [
            'users' => $users,
            'branches' => $formData['branches'],
            'roles' => $formData['roles'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        $this->users->create($validated);

        return back()->with('success', 'Employee created successfully. Login details were sent to the employee email.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);

        $this->users->update($user, $validated);

        return back()->with('success', 'Employee updated successfully.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        try {
            $this->users->resetPassword($user);

            return back()->with('success', 'Password reset successfully. New login details were sent to employee email.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggle(User $user): RedirectResponse
    {
        try {
            $this->users->toggle($user);

            return back()->with('success', 'Employee status updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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