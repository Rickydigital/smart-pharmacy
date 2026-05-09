@can('user.manage')
    <div class="modal fade super-modal" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="{{ route('users.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Create Employee</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information-outline"></i>
                        Username and password will be generated automatically and sent to the employee email.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="super-form-label">First Name</label>
                            <input type="text"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   class="form-control super-form-control"
                                   placeholder="Enter first name"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="super-form-label">Last Name</label>
                            <input type="text"
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   class="form-control super-form-control"
                                   placeholder="Enter last name">
                        </div>

                        <div class="col-md-6">
                            <label class="super-form-label">Email</label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   class="form-control super-form-control"
                                   placeholder="Enter email"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="super-form-label">Phone</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   class="form-control super-form-control"
                                   placeholder="Enter phone">
                        </div>

                        <div class="col-md-6">
                            <label class="super-form-label">Branch</label>
                            <select name="branch_id" class="form-control super-form-control">
                                <option value="">No branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="super-form-label">Role</label>
                            <select name="role" class="form-control super-form-control" required>
                                <option value="">Select role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="super-form-label">Status</label>
                            <select name="status" class="form-control super-form-control" required>
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="super-btn" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="super-btn super-btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
@endcan

@foreach($users as $user)
    @php
        $roleName = $user->roles->first()?->name ?? 'No Role';
        $statusClass = match($user->status) {
            'active' => 'badge-green',
            'inactive' => 'badge-yellow',
            'blocked' => 'badge-red',
            default => 'badge-blue',
        };
    @endphp

    <div class="modal fade super-modal" id="showUserModal{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Employee Details</h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="super-item mb-3">
                        <span class="super-thumb"><i class="mdi mdi-account-outline"></i></span>
                        <div>
                            <div class="super-item-title">{{ $user->displayName() }}</div>
                            <div class="super-item-sub">{{ $user->username }}</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-muted fw-bold small">Email</div>
                            <div class="fw-bold">{{ $user->email }}</div>
                        </div>

                        <div class="col-6">
                            <div class="text-muted fw-bold small">Phone</div>
                            <div class="fw-bold">{{ $user->phone ?: 'No phone' }}</div>
                        </div>

                        <div class="col-6">
                            <div class="text-muted fw-bold small">Branch</div>
                            <div class="fw-bold">{{ $user->branch?->name ?: 'No branch' }}</div>
                        </div>

                        <div class="col-6">
                            <div class="text-muted fw-bold small">Role</div>
                            <span class="super-badge badge-blue">{{ $roleName }}</span>
                        </div>

                        <div class="col-6">
                            <div class="text-muted fw-bold small">Status</div>
                            <span class="super-badge {{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                        </div>

                        <div class="col-6">
                            <div class="text-muted fw-bold small">Created</div>
                            <div class="fw-bold">{{ $user->created_at?->format('M d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('user.manage')
        <div class="modal fade super-modal" id="editUserModal{{ $user->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form method="POST" action="{{ route('users.update', $user) }}" class="modal-content">
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Employee</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-light">
                            <i class="mdi mdi-account-key-outline"></i>
                            Username: <strong>{{ $user->username }}</strong>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="super-form-label">First Name</label>
                                <input type="text"
                                       name="first_name"
                                       value="{{ old('first_name', $user->first_name) }}"
                                       class="form-control super-form-control"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Last Name</label>
                                <input type="text"
                                       name="last_name"
                                       value="{{ old('last_name', $user->last_name) }}"
                                       class="form-control super-form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Email</label>
                                <input type="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       class="form-control super-form-control"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Phone</label>
                                <input type="text"
                                       name="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       class="form-control super-form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Branch</label>
                                <select name="branch_id" class="form-control super-form-control">
                                    <option value="">No branch</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Role</label>
                                <select name="role" class="form-control super-form-control" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role', $roleName) === $role->name ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="super-form-label">Status</label>
                                <select name="status" class="form-control super-form-control" required>
                                    <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="blocked" {{ old('status', $user->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="super-btn" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="super-btn super-btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade super-modal" id="resetPasswordModal{{ $user->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('users.password', $user) }}" class="modal-content">
                    @csrf
                    @method('PATCH')

                    <div class="modal-header">
                        <h5 class="modal-title">Reset Password</h5>
                        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="mb-2">
                            Generate a new password for <strong>{{ $user->displayName() }}</strong>?
                        </p>

                        <div class="alert alert-warning mb-0">
                            <i class="mdi mdi-alert-outline"></i>
                            The new login details will be sent to the employee email.
                        </div>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="super-btn" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="super-btn super-btn-primary">Reset & Send</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan
@endforeach