@php
    use App\Models\InventoryAlert;

    $user = auth()->user();

    $displayName = $user?->full_name
        ?: trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? ''));

    $displayName = $displayName ?: ($user?->name ?? $user?->username ?? 'User');

    $nameParts = preg_split('/\s+/', trim($displayName));
    $initials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1));

    if (count($nameParts) > 1) {
        $initials .= strtoupper(substr(end($nameParts), 0, 1));
    }

    $roleName = $user?->roles?->first()?->name ?? 'No Role';

    $isAdminOrOwner = $user?->hasAnyRole(['Owner', 'Admin']) ?? false;

    /*
    |--------------------------------------------------------------------------
    | Inventory Alert Counter
    |--------------------------------------------------------------------------
    | This counts unresolved inventory alerts:
    | open + read = still needs attention.
    */
    $topbarAlertCount = 0;
    $topbarAlerts = collect();

    if ($user?->can('inventory_alert.view')) {
        $topbarAlertBaseQuery = InventoryAlert::query()
            ->with(['branch', 'product', 'inventory'])
            ->where('pharmacy_id', $user->pharmacy_id)
            ->when(! $isAdminOrOwner, function ($query) use ($user) {
                $query->where('branch_id', $user->branch_id);
            })
            ->whereIn('status', ['open', 'read']);

        $topbarAlertCount = (clone $topbarAlertBaseQuery)->count();

        $topbarAlerts = (clone $topbarAlertBaseQuery)
            ->latest()
            ->limit(5)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | System Messages Counter
    |--------------------------------------------------------------------------
    | This uses Laravel database notifications.
    | It will count approvals, transfers, daily closings, purchase review, etc.
    */
    $topbarUnreadMessages = $user?->unreadNotifications()->count() ?? 0;

    $topbarMessages = $user
        ? $user->unreadNotifications()->latest()->limit(5)->get()
        : collect();
@endphp

<!-- Topbar Start -->
<div class="navbar-custom premium-topbar">
    <div class="topbar-left-area">
        <button class="button-menu-mobile topbar-icon-btn" type="button" aria-label="Toggle sidebar">
            <i class="mdi mdi-menu"></i>
        </button>

        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="topbar-logo-sm ml-1 d-lg-none">
            <img src="{{ asset('app-assets/images/logo-sm.png') }}" height="32" alt="Logo">
        </a>

        <div class="topbar-page-info ml-2">
            <span class="topbar-page-kicker">@yield('page-kicker', 'Dashboard')</span>
            <h5>@yield('page-title', 'Overview')</h5>
            <small>@yield('page-subtitle', 'Welcome back, here is what is happening today.')</small>
        </div>

        <div class="topbar-search quick-search-wrap">
    <i class="mdi mdi-magnify search-icon"></i>
    <input type="text"
           class="js-quick-search-input"
           placeholder="Search product, receipt, batch, transfer..."
           autocomplete="off">
    <span class="search-shortcut">⌘K</span>

    <div class="quick-search-panel js-quick-search-panel">
        <div class="quick-search-empty">Type at least 2 characters to search.</div>
    </div>
</div>
    </div>

    <div class="topbar-right-area">
        <button type="button" class="topbar-icon-btn mobile-search-btn" id="mobileSearchToggle" aria-label="Search">
            <i class="mdi mdi-magnify"></i>
        </button>

        @if(Route::has('pos.index'))
            @can('pos.use')
                <a href="{{ route('pos.index') }}"
                   class="btn btn-primary d-none d-md-inline-flex align-items-center"
                   style="height:42px;border-radius:14px;font-weight:850;box-shadow:0 12px 22px rgba(37,99,235,.20);">
                    <i class="mdi mdi-cash-register mr-1"></i>
                    POS
                </a>
            @endcan
        @endif

        {{-- INVENTORY ALERTS --}}
        @can('inventory_alert.view')
            <div class="topbar-dropdown">
                <button type="button" class="topbar-icon-btn js-topbar-dropdown" aria-label="Inventory Alerts">
                    <i class="mdi mdi-bell-outline"></i>

                    @if($topbarAlertCount > 0)
                        <span class="topbar-badge">
                            {{ $topbarAlertCount > 99 ? '99+' : $topbarAlertCount }}
                        </span>
                    @endif
                </button>

                <div class="topbar-dropdown-menu premium-dropdown">
                    <div class="dropdown-header border-0 pb-2 px-2">
                        <div class="fw-bold text-dark">Inventory Alerts</div>
                        <small class="text-muted">
                            {{ number_format($topbarAlertCount) }} unresolved alert{{ $topbarAlertCount === 1 ? '' : 's' }}
                        </small>
                    </div>

                    <div class="px-2 py-2">
                        @forelse($topbarAlerts as $alert)
                            @php
                                $icon = match ($alert->alert_type) {
                                    'expired' => 'mdi-alert-octagon-outline',
                                    'out_of_stock' => 'mdi-package-variant-remove',
                                    'expiring_soon' => 'mdi-calendar-alert-outline',
                                    'low_stock' => 'mdi-warehouse',
                                    default => 'mdi-bell-alert-outline',
                                };

                                $iconBg = match ($alert->severity) {
                                    'critical' => 'linear-gradient(135deg,#dc2626,#991b1b)',
                                    'high' => 'linear-gradient(135deg,#f97316,#ea580c)',
                                    'medium' => 'linear-gradient(135deg,#2563eb,#1d4ed8)',
                                    default => 'linear-gradient(135deg,#64748b,#475569)',
                                };

                                $alertUrl = Route::has('inventory-alerts.index')
                                    ? route('inventory-alerts.index', [
                                        'status' => $alert->status,
                                        'alert_type' => $alert->alert_type,
                                    ])
                                    : 'javascript:void(0);';
                            @endphp

                            <a href="{{ $alertUrl }}" class="d-flex align-items-start gap-2 py-2 text-decoration-none">
                                <span class="avatar-initials"
                                      style="width:34px;height:34px;border-radius:12px;background:{{ $iconBg }};">
                                    <i class="mdi {{ $icon }}"></i>
                                </span>

                                <div>
                                    <div class="fw-bold small text-dark">
                                        {{ $alert->title }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ \Illuminate\Support\Str::limit($alert->message, 72) }}
                                    </div>

                                    <div class="small text-muted mt-1">
                                        {{ $alert->branch?->name ?: ($alert->meta['branch_name'] ?? '-') }}
                                        @if($alert->created_at)
                                            • {{ $alert->created_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="px-2 py-3 text-muted small">
                                No unresolved inventory alerts.
                            </div>
                        @endforelse
                    </div>

                    @if(Route::has('inventory-alerts.index'))
                        <div class="dropdown-divider"></div>

                        <a href="{{ route('inventory-alerts.index', ['status' => 'open']) }}" class="premium-dropdown-item">
                            <i class="mdi mdi-bell-check-outline"></i>
                            View all alerts
                        </a>
                    @endif
                </div>
            </div>
        @endcan

        {{-- SYSTEM MESSAGES --}}
        <div class="topbar-dropdown d-none d-md-block">
            <button type="button" class="topbar-icon-btn js-topbar-dropdown" aria-label="Messages">
                <i class="mdi mdi-message-processing-outline"></i>

                @if($topbarUnreadMessages > 0)
                    <span class="topbar-badge is-blue">
                        {{ $topbarUnreadMessages > 99 ? '99+' : $topbarUnreadMessages }}
                    </span>
                @endif
            </button>

            <div class="topbar-dropdown-menu premium-dropdown">
                <div class="dropdown-header border-0 pb-2 px-2">
                    <div class="fw-bold text-dark">System Messages</div>
                    <small class="text-muted">
                        {{ number_format($topbarUnreadMessages) }} unread message{{ $topbarUnreadMessages === 1 ? '' : 's' }}
                    </small>
                </div>

                <div class="px-2 py-2">
                    @forelse($topbarMessages as $notification)
                        @php
                            $data = $notification->data ?? [];

                            $messageUrl = $data['url'] ?? 'javascript:void(0);';
                            $messageTitle = $data['title'] ?? 'System message';
                            $messageText = $data['message'] ?? '';
                            $messageType = $data['type'] ?? 'system';
                            $severity = $data['severity'] ?? 'info';

                            $messageIcon = match ($messageType) {
                                'daily_closing' => 'mdi-cash-clock',
                                'expense' => 'mdi-cash-minus',
                                'purchase' => 'mdi-cart-arrow-down',
                                'sales_return' => 'mdi-backup-restore',
                                'stock_adjustment' => 'mdi-clipboard-edit-outline',
                                'stock_transfer' => 'mdi-swap-horizontal-bold',
                                default => 'mdi-message-alert-outline',
                            };

                            $messageIconBg = match ($severity) {
                                'critical' => 'linear-gradient(135deg,#dc2626,#991b1b)',
                                'warning' => 'linear-gradient(135deg,#f97316,#ea580c)',
                                'success' => 'linear-gradient(135deg,#16a34a,#15803d)',
                                default => 'linear-gradient(135deg,#2563eb,#1d4ed8)',
                            };
                        @endphp

                        <a href="{{ $messageUrl }}" class="d-flex align-items-start gap-2 py-2 text-decoration-none">
                            <span class="avatar-initials"
                                  style="width:34px;height:34px;border-radius:12px;background:{{ $messageIconBg }};">
                                <i class="mdi {{ $messageIcon }}"></i>
                            </span>

                            <div>
                                <div class="fw-bold small text-dark">
                                    {{ $messageTitle }}
                                </div>

                                <div class="small text-muted">
                                    {{ \Illuminate\Support\Str::limit($messageText, 80) }}
                                </div>

                                <div class="small text-muted mt-1">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-2 py-3 text-muted small">
                            No unread messages.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- USER DROPDOWN --}}
        <div class="topbar-dropdown topbar-user-dropdown">
            <button type="button" class="nav-user premium-user-trigger js-topbar-dropdown" aria-label="User menu">
                <span class="avatar-initials">{{ $initials }}</span>

                <span class="user-meta d-none d-xl-flex">
                    <span class="user-name">{{ $displayName }}</span>
                    <span class="user-role">{{ $roleName }}</span>
                </span>

                <i class="mdi mdi-chevron-down ml-1 d-none d-xl-inline-block user-chevron"></i>
            </button>

            <div class="topbar-dropdown-menu premium-dropdown premium-profile-dropdown">
                <div class="premium-profile-header">
                    <div class="premium-profile-avatar">{{ $initials }}</div>
                    <strong>{{ $displayName }}</strong><br>
                    <small class="text-muted">{{ $roleName }}</small>
                </div>

                <div class="dropdown-divider"></div>

                @if(Route::has('profile.edit'))
                    <a href="{{ route('profile.edit') }}" class="premium-dropdown-item">
                        <i class="mdi mdi-account-outline"></i>
                        My Profile
                    </a>
                @endif

                @if(Route::has('dashboard'))
                    @can('dashboard.view')
                        <a href="{{ route('dashboard') }}" class="premium-dropdown-item">
                            <i class="mdi mdi-view-dashboard-outline"></i>
                            Dashboard
                        </a>
                    @endcan
                @endif

                @if(Route::has('setup.index'))
                    @can('setting.view')
                        <a href="{{ route('setup.index') }}" class="premium-dropdown-item">
                            <i class="mdi mdi-cog-outline"></i>
                            Setup
                        </a>
                    @endcan
                @endif

                @if(Route::has('pos.index'))
                    @can('pos.use')
                        <a href="{{ route('pos.index') }}" class="premium-dropdown-item">
                            <i class="mdi mdi-cash-register"></i>
                            Smart POS
                        </a>
                    @endcan
                @endif

                @if(Route::has('inventory-alerts.index'))
                    @can('inventory_alert.view')
                        <a href="{{ route('inventory-alerts.index', ['status' => 'open']) }}" class="premium-dropdown-item">
                            <i class="mdi mdi-bell-alert-outline"></i>
                            Inventory Alerts

                            @if($topbarAlertCount > 0)
                                <span class="ml-auto badge badge-danger">
                                    {{ $topbarAlertCount > 99 ? '99+' : $topbarAlertCount }}
                                </span>
                            @endif
                        </a>
                    @endcan
                @endif

                <div class="dropdown-divider"></div>

                @if(Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="premium-dropdown-item text-danger w-100 border-0 bg-transparent">
                            <i class="mdi mdi-logout"></i>
                            Logout
                        </button>
                    </form>
                @else
                    <a href="javascript:void(0);" class="premium-dropdown-item text-danger">
                        <i class="mdi mdi-logout"></i>
                        Logout route missing
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- Topbar End -->

<div class="mobile-search-panel" id="mobileSearchPanel">
    <div class="mobile-search-box">
        <i class="mdi mdi-magnify"></i>
        <input type="text"
       class="js-quick-search-input"
       placeholder="Search product, receipt, batch..."
       autocomplete="off">
    </div>

    <div class="quick-search-panel mobile-quick-search-panel js-quick-search-panel">
        <div class="quick-search-empty">Type at least 2 characters to search.</div>
    </div>
</div>