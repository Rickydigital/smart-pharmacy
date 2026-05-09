@extends('components.main-layout')

@section('title', 'Dashboard')
@section('page-title', 'Overview')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ── Reset & Base ─────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

:root {
    --brand:     #2563eb;
    --brand-dk:  #1d4ed8;
    --green:     #10b981;
    --amber:     #f59e0b;
    --rose:      #f43f5e;
    --purple:    #8b5cf6;
    --surface:   #ffffff;
    --bg:        #f1f5f9;
    --border:    rgba(15,23,42,.08);
    --text:      #0f172a;
    --muted:     #64748b;
    --hint:      #94a3b8;
    --radius-sm: 12px;
    --radius-md: 18px;
    --radius-lg: 26px;
    --shadow-sm: 0 2px 8px rgba(15,23,42,.06);
    --shadow-md: 0 8px 24px rgba(15,23,42,.08);
    --shadow-lg: 0 20px 48px rgba(15,23,42,.10);
}

body { background: var(--bg); font-family: 'DM Sans', sans-serif; }

.db-wrap { padding: 0 0 48px; }

/* ── Header ───────────────────────────────────── */
.db-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    padding: 28px 0 24px;
}

.db-greeting-eyebrow {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--brand);
    margin-bottom: 6px;
}

.db-greeting-title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(1.6rem, 4vw, 2.4rem);
    font-weight: 800;
    color: var(--text);
    letter-spacing: -.04em;
    line-height: 1.1;
    margin: 0 0 6px;
}

.db-greeting-sub {
    font-size: 13px;
    color: var(--muted);
    font-weight: 400;
    margin: 0;
    max-width: 420px;
}

/* ── Filter Form ──────────────────────────────── */
.db-filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 8px;
}

.db-filter-group label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--hint);
    margin-bottom: 5px;
}

.db-filter-group select,
.db-filter-group input[type="date"] {
    height: 40px;
    padding: 0 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--surface);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 500;
    color: var(--text);
    min-width: 150px;
    box-shadow: var(--shadow-sm);
    outline: none;
    transition: border-color .2s;
}

.db-filter-group select:focus,
.db-filter-group input[type="date"]:focus {
    border-color: var(--brand);
}

.db-filter-btn {
    height: 40px;
    padding: 0 18px;
    border-radius: var(--radius-sm);
    background: var(--brand);
    color: #fff;
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 4px 14px rgba(37,99,235,.35);
    transition: background .2s, transform .15s;
}

.db-filter-btn:hover { background: var(--brand-dk); transform: translateY(-1px); }
.db-filter-btn:active { transform: scale(.97); }

/* ── KPI Slider ───────────────────────────────── */
.kpi-track-wrap {
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
}

.kpi-track {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 4px;
}

.kpi-track::-webkit-scrollbar { display: none; }

.kpi-card {
    flex: 0 0 calc(50% - 7px);
    scroll-snap-align: start;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 18px 20px;
    box-shadow: var(--shadow-sm);
    position: relative;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    min-width: 180px;
}

.kpi-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 3px 3px 0 0;
}

.kpi-card.kc-blue::before  { background: linear-gradient(90deg, #2563eb, #60a5fa); }
.kpi-card.kc-green::before { background: linear-gradient(90deg, #10b981, #6ee7b7); }
.kpi-card.kc-amber::before { background: linear-gradient(90deg, #f59e0b, #fcd34d); }
.kpi-card.kc-rose::before  { background: linear-gradient(90deg, #f43f5e, #fb7185); }

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.kpi-icon {
    width: 40px; height: 40px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    margin-bottom: 14px;
}

.kc-blue  .kpi-icon { background: #eff6ff; color: #2563eb; }
.kc-green .kpi-icon { background: #ecfdf5; color: #10b981; }
.kc-amber .kpi-icon { background: #fffbeb; color: #f59e0b; }
.kc-rose  .kpi-icon { background: #fff1f2; color: #f43f5e; }

.kpi-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--hint);
    margin-bottom: 6px;
}

.kpi-value {
    font-family: 'Syne', sans-serif;
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -.04em;
    line-height: 1;
    margin-bottom: 8px;
}

.kpi-note {
    font-size: 12px;
    color: var(--hint);
    font-weight: 500;
}

/* Dots indicator */
.kpi-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 14px;
}

.kpi-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--border);
    transition: background .3s, width .3s;
    cursor: pointer;
}

.kpi-dot.active {
    background: var(--brand);
    width: 18px;
    border-radius: 3px;
}

/* ── Main grid ────────────────────────────────── */
.db-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    margin-bottom: 20px;
    align-items: start;
}

/* ── Panel ────────────────────────────────────── */
.panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 22px;
    border-bottom: 1px solid var(--border);
}

.panel-title {
    font-family: 'Syne', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    letter-spacing: -.02em;
    margin: 0 0 2px;
}

.panel-sub {
    font-size: 12px;
    color: var(--hint);
    font-weight: 500;
    margin: 0;
}

.panel-link {
    font-size: 12px;
    font-weight: 700;
    color: var(--brand);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}

.panel-link:hover { text-decoration: underline; }

.panel-body { padding: 18px 22px; }

/* ── Chart wrapper ────────────────────────────── */
#trend-chart { height: 300px; position: relative; }

/* ── Legend ───────────────────────────────────── */
.chart-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    padding: 10px 22px 18px;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
}

.legend-dot {
    width: 10px; height: 10px;
    border-radius: 2px;
    flex-shrink: 0;
}

/* ── Top Products ─────────────────────────────── */
.product-list { display: flex; flex-direction: column; gap: 14px; }

.product-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: var(--radius-sm);
    transition: background .15s;
}

.product-item:hover { background: #f8fafc; }

.product-rank {
    font-family: 'Syne', sans-serif;
    font-size: 11px;
    font-weight: 800;
    color: var(--hint);
    width: 18px;
    text-align: center;
    flex-shrink: 0;
}

.product-item:first-child .product-rank { color: #f59e0b; }
.product-item:nth-child(2) .product-rank { color: var(--hint); }

.product-icon {
    width: 38px; height: 38px;
    border-radius: 12px;
    background: #eff6ff;
    color: var(--brand);
    display: flex; align-items: center; justify-content: center;
    font-size: 17px;
    flex-shrink: 0;
}

.product-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-meta { font-size: 11px; color: var(--hint); font-weight: 500; }

.product-info { flex: 1; min-width: 0; }

.product-right { text-align: right; flex-shrink: 0; }

.product-amount {
    font-size: 13px;
    font-weight: 800;
    color: var(--text);
    margin-bottom: 2px;
}

.product-sold {
    font-size: 11px;
    font-weight: 700;
    color: var(--green);
}

/* ── Bottom grid ──────────────────────────────── */
.db-grid-bottom {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 20px;
    align-items: start;
}

/* ── Table ────────────────────────────────────── */
.super-table { width: 100%; border-collapse: collapse; margin: 0; }

.super-table thead th {
    padding: 0 0 12px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--hint);
    border-bottom: 1px solid var(--border);
    background: transparent;
}

.super-table tbody td {
    padding: 14px 0;
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
    border-bottom: 1px solid #f8fafc;
    vertical-align: middle;
}

.super-table tbody tr:last-child td { border-bottom: none; }

.super-table tbody tr:hover td { background: #f8fafc; }

.sale-no {
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    color: var(--brand);
    font-size: 13px;
}

.sale-date { font-size: 11px; color: var(--hint); font-weight: 500; margin-top: 2px; }

.status-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
}

.status-completed { background: #ecfdf5; color: #059669; }
.status-processing { background: #eff6ff; color: var(--brand); }
.status-pending    { background: #fff7ed; color: #d97706; }

/* ── Activity Feed ────────────────────────────── */
.activity-filters {
    display: flex;
    gap: 8px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.activity-filters select,
.activity-filters input[type="text"] {
    height: 36px;
    padding: 0 11px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #f8fafc;
    font-family: 'DM Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    color: var(--text);
    outline: none;
    transition: border-color .2s, background .2s;
    flex: 1;
    min-width: 90px;
}

.activity-filters select:focus,
.activity-filters input[type="text"]:focus {
    border-color: var(--brand);
    background: #fff;
}

.activity-filter-btn {
    height: 36px;
    width: 36px;
    border-radius: 10px;
    background: var(--brand);
    color: #fff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    transition: background .2s;
}

.activity-filter-btn:hover { background: var(--brand-dk); }

.activity-scroll {
    display: flex;
    flex-direction: column;
    gap: 0;
    max-height: 420px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--border) transparent;
    padding-right: 4px;
}

.activity-scroll::-webkit-scrollbar { width: 4px; }
.activity-scroll::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px; }

.activity-item {
    display: flex;
    gap: 12px;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}

.activity-item:last-child { border-bottom: none; }

.activity-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    border: 1px solid #bfdbfe;
    color: var(--brand);
    font-size: 12px;
    font-weight: 800;
    font-family: 'Syne', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-content { flex: 1; min-width: 0; }

.activity-who {
    font-size: 13px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.activity-event-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 7px;
    background: #eff6ff;
    color: var(--brand);
    border-radius: 999px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
}

.activity-desc {
    font-size: 12px;
    color: var(--muted);
    line-height: 1.5;
    margin-bottom: 4px;
}

.activity-time {
    font-size: 11px;
    font-weight: 600;
    color: var(--hint);
}

/* ── Empty state ──────────────────────────────── */
.empty-state {
    text-align: center;
    padding: 28px 0;
    color: var(--hint);
    font-size: 13px;
    font-weight: 500;
}

/* ── Mobile overrides ─────────────────────────── */
@media (max-width: 991.98px) {
    .kpi-card { flex: 0 0 calc(45% - 7px); }

    .db-grid,
    .db-grid-bottom {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 575.98px) {
    .kpi-card { flex: 0 0 calc(80% - 7px); }

    .db-header { flex-direction: column; align-items: flex-start; }
    .db-filter-form { width: 100%; }
    .db-filter-group select,
    .db-filter-group input[type="date"] { min-width: 0; width: 100%; }
    .db-filter-group { width: 100%; }
    .db-filter-btn { width: 100%; justify-content: center; }

    .panel-head { flex-wrap: wrap; }
    .panel-body { padding: 14px 16px; }
    .panel-head { padding: 14px 16px; }

    .super-table thead th:nth-child(2),
    .super-table tbody td:nth-child(2) { display: none; }
}

/* ── Auto-slide animation ─────────────────────── */
@keyframes slide-in {
    from { opacity: 0; transform: translateX(12px); }
    to   { opacity: 1; transform: translateX(0); }
}

.kpi-card { animation: slide-in .4s ease both; }
.kpi-card:nth-child(1) { animation-delay: .05s; }
.kpi-card:nth-child(2) { animation-delay: .10s; }
.kpi-card:nth-child(3) { animation-delay: .15s; }
.kpi-card:nth-child(4) { animation-delay: .20s; }
</style>
@endpush

@section('content')
<div class="db-wrap">

    {{-- ── Header ── --}}
    <div class="db-header">
        <div>
            <div class="db-greeting-eyebrow">
                <i class="mdi mdi-pulse"></i> Live Dashboard
            </div>
            <h1 class="db-greeting-title">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }},
                {{ auth()->user()?->displayName() ?: auth()->user()?->username }}.
            </h1>
            <p class="db-greeting-sub">
                Here's your business overview — sales, profit, expenses &amp; activity in real-time.
            </p>
        </div>

        <form method="GET" action="{{ route('dashboard') }}" class="db-filter-form">
            @if($isAdminOrOwner)
                <div class="db-filter-group">
                    <label>Branch</label>
                    <select name="branch_id">
                        <option value="">All branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string)$branchId === (string)$branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="db-filter-group">
                <label>From</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}">
            </div>

            <div class="db-filter-group">
                <label>To</label>
                <input type="date" name="date_to" value="{{ $dateTo }}">
            </div>

            <div class="db-filter-group" style="margin-top: auto;">
                <button type="submit" class="db-filter-btn">
                    <i class="mdi mdi-filter-outline"></i> Apply
                </button>
            </div>
        </form>
    </div>

    {{-- ── KPI Slider ── --}}
    <div class="kpi-track-wrap">
        <div class="kpi-track" id="kpiTrack">

            <div class="kpi-card kc-blue">
                <div class="kpi-icon"><i class="mdi mdi-finance"></i></div>
                <div class="kpi-label">Total Revenue</div>
                <div class="kpi-value">{{ number_format((float)$summary['sales_total'], 2) }}</div>
                <div class="kpi-note">{{ number_format((int)$summary['sales_count']) }} sale(s) completed</div>
            </div>

            <div class="kpi-card kc-green">
                <div class="kpi-icon"><i class="mdi mdi-chart-line-variant"></i></div>
                <div class="kpi-label">Gross Profit</div>
                <div class="kpi-value">{{ number_format((float)$summary['gross_profit'], 2) }}</div>
                <div class="kpi-note">Cost sold {{ number_format((float)$summary['cost_sold'], 2) }}</div>
            </div>

            <div class="kpi-card kc-amber">
                <div class="kpi-icon"><i class="mdi mdi-cash-minus"></i></div>
                <div class="kpi-label">Expenses</div>
                <div class="kpi-value">{{ number_format((float)$summary['expense_total'], 2) }}</div>
                <div class="kpi-note">Paid operating expenses</div>
            </div>

            <div class="kpi-card kc-rose">
                <div class="kpi-icon"><i class="mdi mdi-calculator-variant-outline"></i></div>
                <div class="kpi-label">Net Profit</div>
                <div class="kpi-value">{{ number_format((float)$summary['net_profit'], 2) }}</div>
                <div class="kpi-note">Net margin {{ number_format((float)$summary['net_margin'], 2) }}%</div>
            </div>

        </div>

        {{-- Dot indicators --}}
        <div class="kpi-dots" id="kpiDots">
            <div class="kpi-dot active" data-idx="0"></div>
            <div class="kpi-dot" data-idx="1"></div>
            <div class="kpi-dot" data-idx="2"></div>
            <div class="kpi-dot" data-idx="3"></div>
        </div>
    </div>

    {{-- ── Trend chart + Top Products ── --}}
    <div class="db-grid mb-4">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Revenue Trend</p>
                    <p class="panel-sub">Daily closing · Sales · Profit · Expenses</p>
                </div>
                <span style="font-size: 12px; font-weight: 700; color: var(--hint);">
                    {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }} –
                    {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                </span>
            </div>

            <div class="panel-body" style="padding-bottom: 0;">
                <div id="trend-chart">
                    <canvas id="trendCanvas" role="img"
                        aria-label="Line chart showing daily closing, sales revenue, profit and expenses over the selected period"></canvas>
                </div>
            </div>

            <div class="chart-legend">
                <span class="legend-item"><span class="legend-dot" style="background:#2563eb;"></span>Daily Closing</span>
                <span class="legend-item"><span class="legend-dot" style="background:#10b981;"></span>Revenue</span>
                <span class="legend-item"><span class="legend-dot" style="background:#8b5cf6;"></span>Profit</span>
                <span class="legend-item"><span class="legend-dot" style="background:#f59e0b;"></span>Expenses</span>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Top Products</p>
                    <p class="panel-sub">Best sellers this period</p>
                </div>
                @if(Route::has('reports.profit'))
                    <a href="{{ route('reports.profit', request()->query()) }}" class="panel-link">
                        View report <i class="mdi mdi-arrow-top-right"></i>
                    </a>
                @endif
            </div>

            <div class="panel-body">
                <div class="product-list">
                    @forelse($topProducts as $i => $row)
                        <div class="product-item">
                            <div class="product-rank">
                                @if($i === 0) <i class="mdi mdi-medal" style="color:#f59e0b;font-size:16px;"></i>
                                @elseif($i === 1) <i class="mdi mdi-medal" style="color:#94a3b8;font-size:16px;"></i>
                                @elseif($i === 2) <i class="mdi mdi-medal" style="color:#b45309;font-size:16px;"></i>
                                @else {{ $i + 1 }}
                                @endif
                            </div>
                            <div class="product-icon"><i class="mdi mdi-pill"></i></div>
                            <div class="product-info">
                                <div class="product-name">{{ $row->product?->name ?: '—' }}</div>
                                <div class="product-meta">{{ $row->productUnit?->unit?->name ?: '—' }}</div>
                            </div>
                            <div class="product-right">
                                <div class="product-amount">{{ number_format((float)$row->sales_amount, 2) }}</div>
                                <div class="product-sold">{{ number_format((int)$row->quantity_sold) }} sold</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No product sales found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Sales + Activity Feed ── --}}
    <div class="db-grid-bottom">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Recent Sales</p>
                    <p class="panel-sub">Latest completed transactions</p>
                </div>
                @if(Route::has('reports.sales'))
                    <a href="{{ route('reports.sales', request()->query()) }}" class="panel-link">
                        View all <i class="mdi mdi-arrow-top-right"></i>
                    </a>
                @endif
            </div>

            <div class="panel-body">
                <div class="table-responsive">
                    <table class="super-table">
                        <thead>
                            <tr>
                                <th>Receipt</th>
                                <th>Branch</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                <tr>
                                    <td>
                                        <div class="sale-no">{{ $sale->sale_no }}</div>
                                        <div class="sale-date">
                                            {{ $sale->sold_at?->timezone('Africa/Dar_es_Salaam')->format('d M Y · h:i A') }}
                                        </div>
                                    </td>
                                    <td>{{ $sale->branch?->name ?: '—' }}</td>
                                    <td style="font-family:'Syne',sans-serif;font-weight:700;">
                                        {{ number_format((float)$sale->total_amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="status-pill status-{{ $sale->status }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="empty-state">No recent sales found.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Activity Feed</p>
                    <p class="panel-sub">Live from activity logs</p>
                </div>
            </div>

            <div class="panel-body">
                <form method="GET" action="{{ route('dashboard') }}">
                    <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                    <input type="hidden" name="date_to" value="{{ $dateTo }}">
                    @if($branchId)
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                    @endif

                    <div class="activity-filters">
                        <select name="activity_log">
                            <option value="">All logs</option>
                            @foreach($activityLogNames as $logName)
                                <option value="{{ $logName }}" {{ $activityLog === $logName ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $logName)) }}
                                </option>
                            @endforeach
                        </select>

                        <input type="text"
                               name="activity_search"
                               value="{{ $activitySearch }}"
                               placeholder="Search...">

                        <button type="submit" class="activity-filter-btn">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                    </div>
                </form>

                <div class="activity-scroll">
                    @forelse($activities as $activity)
                        @php
                            $causerName = '—';
                            if ($activity->causer) {
                                $causerName = method_exists($activity->causer, 'displayName')
                                    ? $activity->causer->displayName()
                                    : ($activity->causer->name ?? $activity->causer->username ?? 'User');
                            }
                            $initials = collect(explode(' ', $causerName))
                                ->filter()->map(fn($p) => mb_substr($p,0,1))->take(2)->implode('');
                        @endphp

                        <div class="activity-item">
                            <div class="activity-avatar">{{ $initials ?: 'SY' }}</div>
                            <div class="activity-content">
                                <div class="activity-who">
                                    {{ $causerName }}
                                    <span class="activity-event-badge">
                                        {{ $activity->event ?: $activity->log_name }}
                                    </span>
                                </div>
                                <div class="activity-desc">{{ $activity->description }}</div>
                                <div class="activity-time">
                                    {{ $activity->created_at?->timezone('Africa/Dar_es_Salaam')->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No activity found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ─── Trend Chart ─── */
    const trendData = @json($trendData);

    const labels   = trendData.map(d => d.day);
    const closing  = trendData.map(d => d.closing  || 0);
    const revenue  = trendData.map(d => d.revenue  || 0);
    const profit   = trendData.map(d => d.profit   || 0);
    const expenses = trendData.map(d => d.expenses || 0);

    const lineDefaults = (color, dash = []) => ({
        borderColor: color,
        pointBackgroundColor: color,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
        borderWidth: 2.5,
        borderDash: dash,
        tension: 0.4,
        fill: false,
    });

    new Chart(document.getElementById('trendCanvas'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Daily Closing', data: closing,  ...lineDefaults('#2563eb') },
                { label: 'Revenue',       data: revenue,  ...lineDefaults('#10b981') },
                { label: 'Profit',        data: profit,   ...lineDefaults('#8b5cf6', [6,3]) },
                { label: 'Expenses',      data: expenses, ...lineDefaults('#f59e0b', [4,4]) },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff',
                    borderColor: 'rgba(15,23,42,.12)',
                    borderWidth: 1,
                    titleColor: '#0f172a',
                    bodyColor: '#64748b',
                    padding: 12,
                    cornerRadius: 12,
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${Number(ctx.parsed.y).toLocaleString()}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { family: 'DM Sans', size: 11 },
                        color: '#94a3b8',
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 10,
                    }
                },
                y: {
                    grid: { color: 'rgba(15,23,42,.04)', borderDash: [4,4] },
                    border: { dash: [4,4] },
                    ticks: {
                        font: { family: 'DM Sans', size: 11 },
                        color: '#94a3b8',
                        callback: v => Number(v).toLocaleString(),
                    }
                }
            }
        }
    });

    /* ─── KPI Slide Dots ─── */
    const track = document.getElementById('kpiTrack');
    const dots  = document.querySelectorAll('.kpi-dot');

    function updateDots() {
        const cards = track.querySelectorAll('.kpi-card');
        const sw    = track.scrollLeft;
        const cw    = cards[0]?.offsetWidth + 14 || 1;
        const idx   = Math.round(sw / cw);
        dots.forEach((d, i) => d.classList.toggle('active', i === idx));
    }

    track.addEventListener('scroll', updateDots, { passive: true });

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            const cards = track.querySelectorAll('.kpi-card');
            const cw    = cards[0]?.offsetWidth + 14 || 0;
            track.scrollTo({ left: parseInt(dot.dataset.idx) * cw, behavior: 'smooth' });
        });
    });

    /* ─── Auto-slide on mobile ─── */
    if (window.innerWidth < 768) {
        let autoIdx = 0;
        const cards = track.querySelectorAll('.kpi-card');
        const total = cards.length;

        setInterval(() => {
            autoIdx = (autoIdx + 1) % total;
            const cw = cards[0]?.offsetWidth + 14 || 0;
            track.scrollTo({ left: autoIdx * cw, behavior: 'smooth' });
        }, 3200);
    }

});
</script>
@endpush