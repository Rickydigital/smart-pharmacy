@php
    $user = auth()->user();

    $displayName = $user?->full_name
        ?: trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? ''));

    $displayName = $displayName ?: ($user?->name ?? $user?->username ?? 'User');

    $roleName = $user?->roles?->first()?->name ?? 'Cashier';

    $currentTime = now();

    $canSwitchBranch = $isAdminOrOwner ?? false;
@endphp

<header class="pos-topbar">
    <div class="pos-brand-area">
        <div class="pos-brand">
            <span class="pos-logo-mark">
                <i class="mdi mdi-medical-bag"></i>
            </span>

            <span class="pos-brand-text">
                <strong>{{ $pharmacy->name ?? 'Smart Pharmacy' }}</strong>
                <span>PHARMACY</span>
            </span>
        </div>

        <div class="pos-page-title">
            Point of Sale
        </div>

        @if(Route::has('dashboard'))
            <a href="{{ route('dashboard') }}" class="pos-back-link d-lg-none" title="Back">
                <i class="mdi mdi-arrow-left"></i>
            </a>
        @endif
    </div>

    <div class="pos-topbar-actions">
        <div class="pos-top-card">
            <span class="pos-top-icon">
                <i class="mdi mdi-map-marker"></i>
            </span>

            <div class="pos-top-meta">
                <small>Branch</small>

                @if($canSwitchBranch)
                    <select id="posBranchSelect" class="pos-branch-select">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $defaultBranch?->id === $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <strong>{{ $defaultBranch?->name ?? 'No Branch' }}</strong>
                    <input type="hidden" id="posBranchSelect" value="{{ $defaultBranch?->id }}">
                @endif
            </div>
        </div>

        <div class="pos-top-card">
            <span class="pos-top-icon">
                <i class="mdi mdi-account-outline"></i>
            </span>

            <div class="pos-top-meta">
                <small>{{ $roleName }}</small>
                <strong>{{ $displayName }}</strong>
            </div>
        </div>

        <div class="pos-top-card">
            <span class="pos-top-icon">
                <i class="mdi mdi-calendar-clock"></i>
            </span>

            <div class="pos-top-meta">
                <small>{{ $currentTime->format('M d, Y') }}</small>
                <strong id="posClock">{{ $currentTime->format('h:i A') }}</strong>
            </div>
        </div>

        @if(Route::has('dashboard'))
            <a href="{{ route('dashboard') }}" class="pos-back-link d-none d-lg-inline-flex" title="Back to Dashboard">
                <i class="mdi mdi-view-dashboard-outline"></i>
            </a>
        @endif
    </div>
</header>