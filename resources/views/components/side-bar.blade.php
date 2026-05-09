<!-- Left Sidebar Start -->
<div class="left-side-menu sidebar-premium">
    <div class="sidebar-brand">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="sidebar-brand-link">
            <img class="brand-logo-full" src="{{ asset('app-assets/images/logo.png') }}" alt="{{ config('app.name') }}">
            <img class="brand-logo-sm" src="{{ asset('app-assets/images/logo-sm.png') }}"
                alt="{{ config('app.name') }}">
        </a>

        <button type="button" class="sidebar-mobile-close d-lg-none"
            onclick="document.body.classList.remove('sidebar-open')">
            <i class="mdi mdi-close"></i>
        </button>
    </div>

    <div class="slimscroll-menu">
        <div id="sidebar-menu">
            <ul class="metismenu" id="side-menu">

                {{-- MAIN --}}
                @canany(['dashboard.view', 'pos.use'])
                <li class="menu-title">Main</li>

                @can('dashboard.view')
                @if(Route::has('dashboard'))
                <li class="{{ request()->routeIs('dashboard') ? 'active mm-active' : '' }}">
                    <a href="{{ route('dashboard') }}" title="Dashboard">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                @endif
                @endcan

                @can('pos.use')
                @if(Route::has('pos.index'))
                <li class="{{ request()->routeIs('pos.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('pos.index') }}" title="Smart POS">
                        <i class="mdi mdi-cash-register"></i>
                        <span>Smart POS</span>
                        <span class="sidebar-mini-badge">POS</span>
                    </a>
                </li>
                @endif
                @endcan
                @endcanany

                {{-- SETUP --}}
                @canany(['setting.view', 'setting.manage'])
                <li class="menu-title">Setup</li>

                @php
                $setupOpen = request()->routeIs('setup.*');
                @endphp

                @if(Route::has('setup.index'))
                <li class="{{ $setupOpen ? 'active mm-active' : '' }}">
                    <a href="{{ route('setup.index') }}" title="Pharmacy Setup">
                        <i class="mdi mdi-cog-outline"></i>
                        <span>Pharmacy Setup</span>
                    </a>
                </li>
                @endif
                @endcanany

                {{-- PRODUCTS --}}
                @canany(['product.view', 'product.manage', 'product_type.manage', 'product_category.manage',
                'unit.manage', 'pricing.manage'])
                <li class="menu-title">Products</li>

                @php
                $productOpen = request()->routeIs('product-setup.*');
                @endphp

                <li class="{{ $productOpen ? 'active mm-active' : '' }}">
                    <a href="javascript:void(0);" class="has-arrow"
                        aria-expanded="{{ $productOpen ? 'true' : 'false' }}" title="Product Setup">
                        <i class="mdi mdi-pill"></i>
                        <span>Product Setup</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level {{ $productOpen ? 'mm-show' : '' }}"
                        aria-expanded="{{ $productOpen ? 'true' : 'false' }}">

                        @canany(['product.view', 'product.manage'])
                        @if(Route::has('product-setup.index'))
                        <li class="{{ request()->routeIs('product-setup.index') ? 'active' : '' }}">
                            <a href="{{ route('product-setup.index') }}">
                                <i class="mdi mdi-package-variant-closed me-1"></i>
                                Product Setup
                            </a>
                        </li>
                        @endif
                        @endcanany

                        @can('product_type.manage')
                        @if(Route::has('product-setup.index'))
                        <li>
                            <a href="{{ route('product-setup.index') }}#types">
                                <i class="mdi mdi-shape-outline me-1"></i>
                                Product Types
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('product_category.manage')
                        @if(Route::has('product-setup.index'))
                        <li>
                            <a href="{{ route('product-setup.index') }}#categories">
                                <i class="mdi mdi-format-list-bulleted-type me-1"></i>
                                Categories
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('unit.manage')
                        @if(Route::has('product-setup.index'))
                        <li>
                            <a href="{{ route('product-setup.index') }}#units">
                                <i class="mdi mdi-ruler-square me-1"></i>
                                Units
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('product.manage')
                        @if(Route::has('product-setup.index'))
                        <li>
                            <a href="{{ route('product-setup.index') }}#structure">
                                <i class="mdi mdi-sitemap-outline me-1"></i>
                                Package Structure
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('pricing.manage')
                        @if(Route::has('product-setup.index'))
                        <li>
                            <a href="{{ route('product-setup.index') }}#prices">
                                <i class="mdi mdi-tag-multiple-outline me-1"></i>
                                Pricing Rules
                            </a>
                        </li>
                        @endif
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- SUPPLIERS & PURCHASES --}}
                @canany(['supplier.view', 'supplier.manage', 'purchase.view', 'purchase.create', 'purchase.update',
                'purchase.manage', 'supplier_payment.manage'])
                <li class="menu-title">Purchasing</li>

                @php
                $purchaseOpen = request()->routeIs(
                'suppliers.*',
                'purchases.*',
                'supplier-payments.*'
                );
                @endphp

                <li class="{{ $purchaseOpen ? 'active mm-active' : '' }}">
                    <a href="javascript:void(0);" class="has-arrow"
                        aria-expanded="{{ $purchaseOpen ? 'true' : 'false' }}" title="Suppliers & Purchases">
                        <i class="mdi mdi-truck-delivery-outline"></i>
                        <span>Suppliers & Purchases</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level {{ $purchaseOpen ? 'mm-show' : '' }}"
                        aria-expanded="{{ $purchaseOpen ? 'true' : 'false' }}">
                        @canany(['supplier.view', 'supplier.manage'])
                        @if(Route::has('suppliers.index'))
                        <li class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <a href="{{ route('suppliers.index') }}">
                                <i class="mdi mdi-account-tie-outline me-1"></i>
                                Suppliers
                            </a>
                        </li>
                        @endif
                        @endcanany

                        @canany(['purchase.view', 'purchase.create', 'purchase.update', 'purchase.manage'])
                        @if(Route::has('purchases.index'))
                        <li class="{{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                            <a href="{{ route('purchases.index') }}">
                                <i class="mdi mdi-cart-arrow-down me-1"></i>
                                Purchases
                            </a>
                        </li>
                        @endif
                        @endcanany

                        @can('supplier_payment.manage')
                        @if(Route::has('supplier-payments.index'))
                        <li class="{{ request()->routeIs('supplier-payments.*') ? 'active' : '' }}">
                            <a href="{{ route('supplier-payments.index') }}">
                                <i class="mdi mdi-cash-refund me-1"></i>
                                Supplier Payments
                            </a>
                        </li>
                        @endif
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- INVENTORY --}}
                @canany(['stock.view', 'stock.adjust', 'stock.movement.view', 'expiry.view'])
                <li class="menu-title">Inventory</li>

                @php
                $inventoryOpen = request()->routeIs(
                'inventory.*',
                'inventory-movements.*'
                );
                @endphp

                <li class="{{ $inventoryOpen ? 'active mm-active' : '' }}">
                    <a href="javascript:void(0);" class="has-arrow"
                        aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}" title="Inventory Control">
                        <i class="mdi mdi-warehouse"></i>
                        <span>Inventory Control</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level {{ $inventoryOpen ? 'mm-show' : '' }}"
                        aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}">

                        @can('stock.view')
                        @if(Route::has('inventory.index'))
                        <li class="{{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                            <a href="{{ route('inventory.index') }}">
                                <i class="mdi mdi-package-variant-closed me-1"></i>
                                Current Inventory
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('stock.movement.view')
                        @if(Route::has('inventory-movements.index'))
                        <li class="{{ request()->routeIs('inventory-movements.*') ? 'active' : '' }}">
                            <a href="{{ route('inventory-movements.index') }}">
                                <i class="mdi mdi-swap-horizontal-bold me-1"></i>
                                Inventory Movements
                            </a>
                        </li>
                        @endif
                        @endcan

                        @canany(['stock_adjustment.view', 'stock_adjustment.create', 'stock_adjustment.approve'])
                        @if(Route::has('stock-adjustments.index'))
                        <li class="{{ request()->routeIs('stock-adjustments.*') ? 'active' : '' }}">
                            <a href="{{ route('stock-adjustments.index') }}">
                                <i class="mdi mdi-clipboard-edit-outline me-1"></i>
                                Stock Adjustments
                            </a>
                        </li>
                        @endif
                        @endcanany
                        @canany([
                        'stock_transfer.view',
                        'stock_transfer.create',
                        'stock_transfer.approve',
                        'stock_transfer.dispatch',
                        'stock_transfer.receive'
                        ])
                        @if(Route::has('stock-transfers.index'))
                        <li class="{{ request()->routeIs('stock-transfers.*') ? 'active' : '' }}">
                            <a href="{{ route('stock-transfers.index') }}">
                                <i class="mdi mdi-swap-horizontal-bold me-1"></i>
                                Stock Transfers
                            </a>
                        </li>
                        @endif
                        @endcanany

                        @can('expiry.view')
                        @if(Route::has('inventory.index'))
                        <li
                            class="{{ request()->routeIs('inventory.*') && request('status') === 'expired' ? 'active' : '' }}">
                            <a href="{{ route('inventory.index', ['status' => 'expired']) }}">
                                <i class="mdi mdi-calendar-alert-outline me-1"></i>
                                Expired Inventory
                            </a>
                        </li>

                        <li class="{{ request()->routeIs('inventory.*') && request('expiry_to') ? 'active' : '' }}">
                            <a
                                href="{{ route('inventory.index', ['expiry_from' => now()->toDateString(), 'expiry_to' => now()->addDays(30)->toDateString()]) }}">
                                <i class="mdi mdi-clock-alert-outline me-1"></i>
                                Expiring Soon
                            </a>
                        </li>
                        @endif
                        @endcan
                    </ul>
                </li>
                @endcanany

                @canany(['inventory_alert.view', 'inventory_alert.manage', 'inventory_alert.generate'])
                    @if(Route::has('inventory-alerts.index'))
                        <li class="{{ request()->routeIs('inventory-alerts.*') ? 'active' : '' }}">
                            <a href="{{ route('inventory-alerts.index') }}">
                                <i class="mdi mdi-bell-alert-outline me-1"></i>
                                Inventory Alerts
                            </a>
                        </li>
                    @endif
                @endcanany

                {{-- SALES --}}
                @canany(['sale.view', 'sale.create', 'sale.return', 'sale.cancel', 'receipt.print'])
                <li class="menu-title">Sales</li>

                @php
                $salesOpen = request()->routeIs(
                'sales.*',
                'sale-returns.*',
                'receipts.*'
                );
                @endphp

                <li class="{{ $salesOpen ? 'active mm-active' : '' }}">
                    <a href="javascript:void(0);" class="has-arrow" aria-expanded="{{ $salesOpen ? 'true' : 'false' }}"
                        title="Sales Center">
                        <i class="mdi mdi-receipt-text-outline"></i>
                        <span>Sales Center</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level {{ $salesOpen ? 'mm-show' : '' }}"
                        aria-expanded="{{ $salesOpen ? 'true' : 'false' }}">
                        @can('sale.view')
                        @if(Route::has('sales.index'))
                        <li class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">
                            <a href="{{ route('sales.index') }}">
                                <i class="mdi mdi-format-list-bulleted me-1"></i>
                                Sales History
                            </a>
                        </li>
                        @endif
                        @endcan

                        @canany(['sales_return.view', 'sales_return.create', 'sales_return.approve'])
                        @if(Route::has('sales-returns.index'))
                        <li class="{{ request()->routeIs('sales-returns.*') ? 'active' : '' }}">
                            <a href="{{ route('sales-returns.index') }}" title="Sales Returns">
                                <i class="mdi mdi-backup-restore"></i>
                                <span>Sales Returns</span>
                            </a>
                        </li>
                        @endif
                        @endcanany
                    </ul>
                </li>
                @endcanany

                {{-- PRESCRIPTION --}}
                @canany(['prescription.view', 'prescription.manage'])
                @if(Route::has('prescriptions.index'))
                <li class="menu-title">Prescription</li>

                <li class="{{ request()->routeIs('prescriptions.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('prescriptions.index') }}" title="Prescription Records">
                        <i class="mdi mdi-file-document-edit-outline"></i>
                        <span>Prescription Records</span>
                    </a>
                </li>
                @endif
                @endcanany

                {{-- FINANCE --}}
                @canany(['expense.view', 'expense.manage', 'daily_closing.view', 'daily_closing.manage'])
                <li class="menu-title">Finance</li>

                @php
                $financeOpen = request()->routeIs(
                'expense-categories.*',
                'expenses.*',
                'daily-closings.*'
                );
                @endphp

                <li class="{{ $financeOpen ? 'active mm-active' : '' }}">
                    <a href="javascript:void(0);" class="has-arrow"
                        aria-expanded="{{ $financeOpen ? 'true' : 'false' }}" title="Expenses & Closing">
                        <i class="mdi mdi-cash-multiple"></i>
                        <span>Expenses & Closing</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level {{ $financeOpen ? 'mm-show' : '' }}"
                        aria-expanded="{{ $financeOpen ? 'true' : 'false' }}">

                        @canany(['expense.view', 'expense.manage'])
                        @if(Route::has('expenses.index'))
                        <li class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                            <a href="{{ route('expenses.index') }}">
                                <i class="mdi mdi-cash-minus me-1"></i>
                                Expenses
                            </a>
                        </li>
                        @endif
                        @endcanany

                        @canany(['daily_closing.view', 'daily_closing.manage'])
                        @if(Route::has('daily-closings.index'))
                        <li class="{{ request()->routeIs('daily-closings.*') ? 'active' : '' }}">
                            <a href="{{ route('daily-closings.index') }}">
                                <i class="mdi mdi-calendar-check-outline me-1"></i>
                                Daily Closing
                            </a>
                        </li>
                        @endif
                        @endcanany
                    </ul>
                </li>
                @endcanany

                {{-- REPORTS --}}
                @canany(['report.view', 'report.sales', 'report.stock', 'report.purchase', 'report.profit',
                'report.expense', 'report.prescription'])
                <li class="menu-title">Reports</li>

                @php
                $reportsOpen = request()->routeIs('reports.*');
                @endphp

                <li class="{{ $reportsOpen ? 'active mm-active' : '' }}">
                    <a href="javascript:void(0);" class="has-arrow"
                        aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}" title="Reports">
                        <i class="mdi mdi-chart-box-outline"></i>
                        <span>Reports</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level {{ $reportsOpen ? 'mm-show' : '' }}"
                        aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}">

                        @can('report.view')
                        @if(Route::has('reports.index'))
                        <li class="{{ request()->routeIs('reports.index') ? 'active' : '' }}">
                            <a href="{{ route('reports.index') }}">
                                <i class="mdi mdi-view-dashboard-outline me-1"></i>
                                Report Center
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('report.sales')
                        @if(Route::has('reports.sales'))
                        <li class="{{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                            <a href="{{ route('reports.sales') }}">
                                <i class="mdi mdi-receipt-outline me-1"></i>
                                Sales Report
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('report.stock')
                        @if(Route::has('reports.stock'))
                        <li class="{{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                            <a href="{{ route('reports.stock') }}">
                                <i class="mdi mdi-warehouse me-1"></i>
                                Stock Report
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('report.purchase')
                        @if(Route::has('reports.purchases'))
                        <li class="{{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                            <a href="{{ route('reports.purchases') }}">
                                <i class="mdi mdi-cart-arrow-down me-1"></i>
                                Purchase Report
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('report.profit')
                        @if(Route::has('reports.profit'))
                        <li class="{{ request()->routeIs('reports.profit') ? 'active' : '' }}">
                            <a href="{{ route('reports.profit') }}">
                                <i class="mdi mdi-chart-line me-1"></i>
                                Profit Report
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('report.expense')
                        @if(Route::has('reports.expenses'))
                        <li class="{{ request()->routeIs('reports.expenses') ? 'active' : '' }}">
                            <a href="{{ route('reports.expenses') }}">
                                <i class="mdi mdi-cash-minus me-1"></i>
                                Expense Report
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('report.prescription')
                        @if(Route::has('reports.prescriptions'))
                        <li class="{{ request()->routeIs('reports.prescriptions') ? 'active' : '' }}">
                            <a href="{{ route('reports.prescriptions') }}">
                                <i class="mdi mdi-file-document-edit-outline me-1"></i>
                                Prescription Report
                            </a>
                        </li>
                        @endif
                        @endcan
                    </ul>
                </li>
                @endcanany

                {{-- AI --}}
                @canany(['ai.use', 'ai.use_basic', 'ai.history.view'])
                <li class="menu-title">AI Assistant</li>

                @canany(['ai.use', 'ai.use_basic'])
                @if(Route::has('ai.index'))
                <li class="{{ request()->routeIs('ai.index') ? 'active mm-active' : '' }}">
                    <a href="{{ route('ai.index') }}" title="AI Assistant">
                        <i class="mdi mdi-robot-outline"></i>
                        <span>AI Assistant</span>
                    </a>
                </li>
                @endif
                @endcanany

                @can('ai.history.view')
                @if(Route::has('ai.conversations.index'))
                <li class="{{ request()->routeIs('ai.conversations.*') ? 'active mm-active' : '' }}">
                    <a href="{{ route('ai.conversations.index') }}" title="AI History">
                        <i class="mdi mdi-message-text-clock-outline"></i>
                        <span>AI History</span>
                    </a>
                </li>
                @endif
                @endcan
                @endcanany

                {{-- ADMINISTRATION --}}
                @canany(['user.view', 'user.manage', ''])
                <li class="menu-title">Administration</li>

                @php
                $adminOpen = request()->routeIs(
                'users.*',
                'roles.*',
                'activity-logs.*',
                'audit.*'
                );
                @endphp

                <li class="{{ $adminOpen ? 'active mm-active' : '' }}">
                    <a href="javascript:void(0);" class="has-arrow" aria-expanded="{{ $adminOpen ? 'true' : 'false' }}"
                        title="Access Control">
                        <i class="mdi mdi-shield-account-outline"></i>
                        <span>Access Control</span>
                        <span class="menu-arrow"></span>
                    </a>

                    <ul class="nav-second-level {{ $adminOpen ? 'mm-show' : '' }}"
                        aria-expanded="{{ $adminOpen ? 'true' : 'false' }}">
                        @canany(['user.view', 'user.manage'])
                        @if(Route::has('users.index'))
                        <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}">
                                <i class="mdi mdi-account-group-outline me-1"></i>
                                Users
                            </a>
                        </li>
                        @endif
                        @endcanany

                        @can('user.manage')
                        @if(Route::has('roles.index'))
                        <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <a href="{{ route('roles.index') }}">
                                <i class="mdi mdi-shield-key-outline me-1"></i>
                                Roles
                            </a>
                        </li>
                        @endif
                        @endcan

                        @can('audit.view')
                        @if(Route::has('activity-logs.index'))
                        <li class="{{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                            <a href="{{ route('activity-logs.index') }}">
                                <i class="mdi mdi-history me-1"></i>
                                Activity Logs
                            </a>
                        </li>
                        @endif
                        @endcan
                    </ul>
                </li>
                @endcanany

            </ul>
        </div>

        <div class="sidebar-footer-card d-none d-lg-block">
            <div class="sidebar-footer-icon">
                <i class="mdi mdi-shield-check-outline"></i>
            </div>

            <h6>Secure Console</h6>

            <p>
                Role-based access, clean navigation and responsive pharmacy workflow.
            </p>

            @if(Route::has('dashboard'))
            <a href="{{ route('dashboard') }}" class="btn btn-primary w-100">
                <i class="mdi mdi-view-dashboard-outline me-1"></i>
                Dashboard
            </a>
            @else
            <button type="button" class="btn btn-primary w-100" disabled>
                Dashboard Missing
            </button>
            @endif
        </div>
    </div>
</div>
<!-- Left Sidebar End -->