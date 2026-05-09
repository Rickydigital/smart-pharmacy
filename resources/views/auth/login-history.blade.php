@extends('components.app-main-layout')

@section('title', 'Login History - ' . $user->first_name . ' ' . $user->last_name)

@section('content')
<div class="container-fluid py-4">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 text-primary fw-bold">
            <i class="bi bi-clock-history me-2"></i> Login History
            <small class="text-muted d-block fs-6">{{ $user->first_name }} {{ $user->last_name }}</small>
        </h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <!-- Info Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex align-items-center">
            <div class="me-3">
                <i class="bi bi-person-circle text-primary" style="font-size: 3rem;"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</h5>
                <small class="text-muted">{{ $user->email }}</small>
            </div>
        </div>
    </div>

    <!-- Login History Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3 fw-semibold text-secondary">
            <i class="bi bi-list-ul me-2"></i> Login Records
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Login Date & Time</th>
                        <th>IP Address</th>
                        <th>Device / Browser</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loginHistories as $index => $history)
                        <tr>
                            <td>{{ ($loginHistories->currentPage() - 1) * $loginHistories->perPage() + $index + 1 }}</td>
                            <td>
                                <span class="fw-semibold">{{ $history->login_at->format('d M Y, h:i A') }}</span>
                                <small class="d-block text-muted">{{ $history->login_at->diffForHumans() }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $history->ip_address }}</span>
                            </td>
                            <td>
                                <div class="small text-muted" style="max-width: 400px; white-space: normal;">
                                    {{ $history->user_agent }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-2"></i> No login history found for this user.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($loginHistories->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $loginHistories->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tooltips if needed later
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    });
</script>
@endsection
@endsection
