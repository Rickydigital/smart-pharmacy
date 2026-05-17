@extends('components.main-layout')

@section('title', 'Intelligence')
@section('page-title', 'Pharmacy Intelligence')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --brand:#2563eb;
    --brand-dark:#1d4ed8;
    --green:#10b981;
    --amber:#f59e0b;
    --rose:#f43f5e;
    --purple:#8b5cf6;
    --cyan:#06b6d4;
    --bg:#f1f5f9;
    --surface:#ffffff;
    --border:rgba(15,23,42,.08);
    --text:#0f172a;
    --muted:#64748b;
    --hint:#94a3b8;
    --radius-sm:14px;
    --radius-md:20px;
    --radius-lg:28px;
    --shadow-sm:0 4px 14px rgba(15,23,42,.06);
    --shadow-md:0 16px 40px rgba(15,23,42,.08);
}

body {
    background:var(--bg);
    font-family:'DM Sans',sans-serif;
}

.intel-wrap {
    padding:0 0 50px;
}

.intel-header {
    display:flex;
    justify-content:space-between;
    align-items:flex-end;
    gap:18px;
    flex-wrap:wrap;
    padding:28px 0 24px;
}

.intel-eyebrow {
    font-size:11px;
    font-weight:800;
    letter-spacing:.14em;
    text-transform:uppercase;
    color:var(--brand);
    margin-bottom:6px;
}

.intel-title {
    font-family:'Syne',sans-serif;
    font-size:clamp(1.7rem,4vw,2.6rem);
    font-weight:800;
    letter-spacing:-.05em;
    color:var(--text);
    margin:0 0 8px;
    line-height:1.05;
}

.intel-subtitle {
    font-size:13px;
    color:var(--muted);
    max-width:620px;
    margin:0;
}

.intel-filter {
    display:flex;
    gap:8px;
    align-items:flex-end;
    flex-wrap:wrap;
}

.intel-filter label {
    display:block;
    font-size:10px;
    font-weight:800;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--hint);
    margin-bottom:5px;
}

.intel-filter select,
.intel-filter input {
    height:42px;
    border:1px solid var(--border);
    border-radius:13px;
    background:#fff;
    padding:0 12px;
    font-size:13px;
    font-weight:600;
    color:var(--text);
    box-shadow:var(--shadow-sm);
    outline:none;
    min-width:155px;
}

.intel-btn {
    height:42px;
    border:none;
    border-radius:13px;
    background:var(--brand);
    color:#fff;
    padding:0 18px;
    font-size:13px;
    font-weight:800;
    display:inline-flex;
    align-items:center;
    gap:7px;
    box-shadow:0 10px 24px rgba(37,99,235,.25);
}

.kpi-grid {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:14px;
    margin-bottom:20px;
}

.kpi-card {
    background:#fff;
    border:1px solid var(--border);
    border-radius:22px;
    padding:18px;
    box-shadow:var(--shadow-sm);
    position:relative;
    overflow:hidden;
}

.kpi-card:before {
    content:'';
    position:absolute;
    inset:0 0 auto 0;
    height:4px;
}

.kpi-card.blue:before { background:linear-gradient(90deg,#2563eb,#60a5fa); }
.kpi-card.green:before { background:linear-gradient(90deg,#10b981,#6ee7b7); }
.kpi-card.amber:before { background:linear-gradient(90deg,#f59e0b,#fcd34d); }
.kpi-card.rose:before { background:linear-gradient(90deg,#f43f5e,#fb7185); }

.kpi-icon {
    width:42px;
    height:42px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    margin-bottom:14px;
}

.blue .kpi-icon { background:#eff6ff;color:#2563eb; }
.green .kpi-icon { background:#ecfdf5;color:#10b981; }
.amber .kpi-icon { background:#fffbeb;color:#f59e0b; }
.rose .kpi-icon { background:#fff1f2;color:#f43f5e; }

.kpi-label {
    font-size:11px;
    font-weight:800;
    letter-spacing:.08em;
    text-transform:uppercase;
    color:var(--hint);
    margin-bottom:6px;
}

.kpi-value {
    font-family:'Syne',sans-serif;
    font-size:1.9rem;
    font-weight:800;
    color:var(--text);
    letter-spacing:-.05em;
    line-height:1;
    margin-bottom:8px;
}

.kpi-note {
    font-size:12px;
    color:var(--muted);
    font-weight:600;
}

.intel-grid {
    display:grid;
    grid-template-columns:1fr 360px;
    gap:20px;
    margin-bottom:20px;
}

.intel-grid-2 {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-bottom:20px;
}

.panel {
    background:#fff;
    border:1px solid var(--border);
    border-radius:28px;
    box-shadow:var(--shadow-sm);
    overflow:hidden;
}

.panel-head {
    padding:18px 22px;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.panel-title {
    font-family:'Syne',sans-serif;
    font-size:16px;
    font-weight:800;
    color:var(--text);
    letter-spacing:-.03em;
    margin:0 0 3px;
}

.panel-sub {
    margin:0;
    color:var(--hint);
    font-size:12px;
    font-weight:600;
}

.panel-body {
    padding:18px 22px;
}

.chart-box {
    height:310px;
    position:relative;
}

.chart-box-small {
    height:260px;
    position:relative;
}

.reco-list,
.alert-list,
.product-list {
    display:flex;
    flex-direction:column;
    gap:12px;
}

.reco-card {
    border:1px solid var(--border);
    border-radius:18px;
    padding:14px;
    background:linear-gradient(135deg,#ffffff,#f8fbff);
    display:flex;
    gap:12px;
}

.reco-icon {
    width:38px;
    height:38px;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#eff6ff;
    color:var(--brand);
    flex-shrink:0;
    font-size:18px;
}

.reco-title {
    font-size:13px;
    font-weight:800;
    color:var(--text);
    margin-bottom:4px;
}

.reco-text {
    font-size:12px;
    color:var(--muted);
    line-height:1.45;
}

.reco-score {
    margin-top:8px;
    display:inline-flex;
    align-items:center;
    padding:4px 9px;
    border-radius:999px;
    font-size:11px;
    font-weight:800;
    background:#eff6ff;
    color:var(--brand);
}

.alert-card {
    display:flex;
    gap:12px;
    padding:13px;
    border-radius:18px;
    border:1px solid var(--border);
    background:#fff;
}

.alert-dot {
    width:10px;
    height:10px;
    border-radius:50%;
    margin-top:6px;
    flex-shrink:0;
}

.alert-critical .alert-dot { background:var(--rose); }
.alert-warning .alert-dot { background:var(--amber); }
.alert-info .alert-dot { background:var(--brand); }

.alert-title {
    font-size:13px;
    font-weight:800;
    color:var(--text);
    margin-bottom:3px;
}

.alert-message {
    font-size:12px;
    color:var(--muted);
    line-height:1.45;
}

.alert-meta {
    margin-top:6px;
    font-size:11px;
    color:var(--hint);
    font-weight:700;
}

.product-row {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px;
    border-radius:17px;
    border:1px solid rgba(15,23,42,.06);
    background:#fff;
}

.product-rank {
    width:28px;
    height:28px;
    border-radius:10px;
    background:#f8fafc;
    color:var(--hint);
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Syne',sans-serif;
    font-size:12px;
    font-weight:800;
    flex-shrink:0;
}

.product-icon {
    width:40px;
    height:40px;
    border-radius:14px;
    background:#eff6ff;
    color:var(--brand);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    flex-shrink:0;
}

.product-info {
    flex:1;
    min-width:0;
}

.product-name {
    font-size:13px;
    font-weight:800;
    color:var(--text);
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    margin-bottom:3px;
}

.product-meta {
    font-size:11px;
    color:var(--hint);
    font-weight:700;
}

.product-right {
    text-align:right;
    flex-shrink:0;
}

.product-value {
    font-size:13px;
    font-weight:900;
    color:var(--text);
}

.product-mini {
    font-size:11px;
    font-weight:800;
    color:var(--green);
}

.badge-soft {
    display:inline-flex;
    align-items:center;
    padding:5px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:900;
}

.badge-critical { background:#fff1f2;color:#e11d48; }
.badge-warning { background:#fffbeb;color:#d97706; }
.badge-info { background:#eff6ff;color:#2563eb; }
.badge-success { background:#ecfdf5;color:#059669; }

.table-wrap {
    overflow:auto;
}

.intel-table {
    width:100%;
    border-collapse:collapse;
}

.intel-table th {
    text-align:left;
    font-size:10px;
    font-weight:900;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--hint);
    padding:0 10px 12px;
    border-bottom:1px solid var(--border);
    white-space:nowrap;
}

.intel-table td {
    padding:13px 10px;
    border-bottom:1px solid #f1f5f9;
    font-size:13px;
    color:var(--text);
    font-weight:700;
    vertical-align:middle;
    white-space:nowrap;
}

.empty-state {
    padding:28px 10px;
    text-align:center;
    color:var(--hint);
    font-size:13px;
    font-weight:700;
}

@media(max-width:991px) {
    .kpi-grid,
    .intel-grid,
    .intel-grid-2 {
        grid-template-columns:1fr;
    }
}

@media(max-width:575px) {
    .kpi-grid {
        grid-template-columns:1fr 1fr;
    }

    .panel-head,
    .panel-body {
        padding:15px;
    }

    .intel-filter,
    .intel-filter > div,
    .intel-filter select,
    .intel-filter input,
    .intel-btn {
        width:100%;
    }
}
</style>
@endpush

@section('content')
@php
    $summary = $data['summary'] ?? [];

    $criticalRestock = collect($data['critical_restock'] ?? []);
    $missedDemand = collect($data['missed_demand'] ?? []);
    $nearExpiry = collect($data['near_expiry'] ?? []);
    $slowProducts = collect($data['slow_products'] ?? []);
    $highProfitProducts = collect($data['high_profit_products'] ?? []);
    $topDemandProducts = collect($data['top_demand_products'] ?? []);
    $alerts = collect($data['alerts'] ?? []);

    $recommendations = $criticalRestock
        ->merge($missedDemand)
        ->merge($nearExpiry)
        ->merge($slowProducts)
        ->sortByDesc('priority_score')
        ->take(8)
        ->values();

    $demandLabels = $topDemandProducts->pluck('product.name')->map(fn($v) => $v ?: 'Product')->values();
    $demandValues = $topDemandProducts->pluck('sales_base_units')->map(fn($v) => (int) $v)->values();

    $profitLabels = $highProfitProducts->pluck('product.name')->map(fn($v) => $v ?: 'Product')->values();
    $profitValues = $highProfitProducts->pluck('gross_profit')->map(fn($v) => (float) $v)->values();

    $priorityLabels = $recommendations->pluck('product.name')->map(fn($v) => $v ?: 'Product')->values();
    $priorityValues = $recommendations->pluck('priority_score')->map(fn($v) => (int) $v)->values();

    $riskData = [
        'Critical' => (int)($summary['critical_alerts'] ?? 0),
        'Warning' => (int)($summary['warning_alerts'] ?? 0),
        'Normal' => max(0, (int)($summary['total_products'] ?? 0) - ((int)($summary['critical_alerts'] ?? 0) + (int)($summary['warning_alerts'] ?? 0))),
    ];
@endphp

<div class="intel-wrap">

    <div class="intel-header">
        <div>
            <div class="intel-eyebrow">
                <i class="mdi mdi-brain"></i> Owner Intelligence
            </div>
            <h1 class="intel-title">Smart pharmacy decisions, not guesswork.</h1>
            <p class="intel-subtitle">
                Track product demand, missed customer interest, low stock risk, expiry exposure,
                profit performance and restocking priorities from one intelligent dashboard.
            </p>
        </div>

        <form method="GET" action="{{ route('intelligence.index') }}" class="intel-filter">
            @isset($branches)
                <div>
                    <label>Branch</label>
                    <select name="branch_id">
                        <option value="">All branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ (string)request('branch_id') === (string)$branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endisset

            <div>
                <label>Month</label>
                <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}">
            </div>

            <button type="submit" class="intel-btn">
                <i class="mdi mdi-filter-outline"></i> Apply
            </button>
        </form>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card rose">
            <div class="kpi-icon"><i class="mdi mdi-alert-decagram-outline"></i></div>
            <div class="kpi-label">Critical Alerts</div>
            <div class="kpi-value">{{ number_format((int)($summary['critical_alerts'] ?? 0)) }}</div>
            <div class="kpi-note">Need owner attention</div>
        </div>

        <div class="kpi-card amber">
            <div class="kpi-icon"><i class="mdi mdi-alert-outline"></i></div>
            <div class="kpi-label">Warning Alerts</div>
            <div class="kpi-value">{{ number_format((int)($summary['warning_alerts'] ?? 0)) }}</div>
            <div class="kpi-note">Stock, expiry or price risk</div>
        </div>

        <div class="kpi-card blue">
            <div class="kpi-icon"><i class="mdi mdi-speedometer"></i></div>
            <div class="kpi-label">Average Priority</div>
            <div class="kpi-value">{{ number_format((float)($summary['average_priority'] ?? 0), 1) }}</div>
            <div class="kpi-note">Demand priority score</div>
        </div>

        <div class="kpi-card green">
            <div class="kpi-icon"><i class="mdi mdi-pill-multiple"></i></div>
            <div class="kpi-label">Analysed Products</div>
            <div class="kpi-value">{{ number_format((int)($summary['total_products'] ?? 0)) }}</div>
            <div class="kpi-note">Products in intelligence</div>
        </div>
    </div>

    <div class="intel-grid">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Demand & Priority Intelligence</p>
                    <p class="panel-sub">Products with strongest demand and action priority</p>
                </div>
                <span class="badge-soft badge-info">Live snapshot</span>
            </div>
            <div class="panel-body">
                <div class="chart-box">
                    <canvas id="priorityChart"></canvas>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Risk Overview</p>
                    <p class="panel-sub">Critical, warning and normal product status</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-box-small">
                    <canvas id="riskChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="intel-grid-2">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Top Demand Products</p>
                    <p class="panel-sub">Most sold products by base units</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-box-small">
                    <canvas id="demandChart"></canvas>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">High Profit Products</p>
                    <p class="panel-sub">Products giving stronger gross profit</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="chart-box-small">
                    <canvas id="profitChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="intel-grid">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Smart Recommendations</p>
                    <p class="panel-sub">Human-style owner advice generated from product data</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="reco-list">
                    @forelse($recommendations as $row)
                        @php
                            $type = $row->recommendation_type;
                            $icon = match($type) {
                                'restock' => 'mdi-package-variant-closed-plus',
                                'missed_demand' => 'mdi-account-search-outline',
                                'expired_stock' => 'mdi-alert-octagon-outline',
                                'near_expiry_slow' => 'mdi-clock-alert-outline',
                                'slow_moving' => 'mdi-snail',
                                'price_review' => 'mdi-tag-edit-outline',
                                default => 'mdi-lightbulb-on-outline',
                            };
                        @endphp

                        <div class="reco-card">
                            <div class="reco-icon">
                                <i class="mdi {{ $icon }}"></i>
                            </div>
                            <div>
                                <div class="reco-title">
                                    {{ $row->product?->name ?? 'Product' }}
                                </div>
                                <div class="reco-text">
                                    {{ $row->recommendation_text }}
                                </div>
                                <span class="reco-score">
                                    Priority {{ number_format((int)$row->priority_score) }}/100
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No recommendations found. Generate intelligence snapshots first.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Open Alerts</p>
                    <p class="panel-sub">Important owner alerts</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="alert-list">
                    @forelse($alerts as $alert)
                        <div class="alert-card alert-{{ $alert->severity }}">
                            <div class="alert-dot"></div>
                            <div>
                                <div class="alert-title">{{ $alert->title }}</div>
                                <div class="alert-message">{{ $alert->message }}</div>
                                <div class="alert-meta">
                                    {{ ucfirst($alert->severity) }} · {{ $alert->created_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No open intelligence alerts.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="intel-grid-2">
        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Restock Priority</p>
                    <p class="panel-sub">Products likely to need stock soon</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="product-list">
                    @forelse($criticalRestock as $i => $row)
                        <div class="product-row">
                            <div class="product-rank">{{ $i + 1 }}</div>
                            <div class="product-icon"><i class="mdi mdi-package-variant"></i></div>
                            <div class="product-info">
                                <div class="product-name">{{ $row->product?->name ?? 'Product' }}</div>
                                <div class="product-meta">
                                    Stock cover: {{ $row->stock_cover_days ? number_format((float)$row->stock_cover_days, 1).' days' : 'N/A' }}
                                </div>
                            </div>
                            <div class="product-right">
                                <div class="product-value">{{ number_format((int)$row->current_stock_base_units) }}</div>
                                <div class="product-mini">left</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No critical restock products.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <p class="panel-title">Missed Demand</p>
                    <p class="panel-sub">Customers searched but stock/result was weak</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="product-list">
                    @forelse($missedDemand as $i => $row)
                        <div class="product-row">
                            <div class="product-rank">{{ $i + 1 }}</div>
                            <div class="product-icon"><i class="mdi mdi-magnify"></i></div>
                            <div class="product-info">
                                <div class="product-name">{{ $row->product?->name ?? 'Product' }}</div>
                                <div class="product-meta">
                                    {{ number_format((int)$row->missed_searches) }} missed search(es)
                                </div>
                            </div>
                            <div class="product-right">
                                <div class="product-value">{{ number_format((int)$row->public_searches) }}</div>
                                <div class="product-mini">searches</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">No missed demand found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <p class="panel-title">Product Intelligence Table</p>
                <p class="panel-sub">Detailed ranking by priority, demand, stock, expiry and profit</p>
            </div>
        </div>

        <div class="panel-body">
            <div class="table-wrap">
                <table class="intel-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Priority</th>
                            <th>Sales Units</th>
                            <th>Searches</th>
                            <th>Stock</th>
                            <th>Cover</th>
                            <th>Near Expiry</th>
                            <th>Profit</th>
                            <th>Advice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recommendations as $row)
                            <tr>
                                <td>{{ $row->product?->name ?? 'Product' }}</td>
                                <td>
                                    <span class="badge-soft {{ $row->priority_score >= 80 ? 'badge-critical' : ($row->priority_score >= 50 ? 'badge-warning' : 'badge-info') }}">
                                        {{ number_format((int)$row->priority_score) }}
                                    </span>
                                </td>
                                <td>{{ number_format((int)$row->sales_base_units) }}</td>
                                <td>{{ number_format((int)$row->public_searches) }}</td>
                                <td>{{ number_format((int)$row->current_stock_base_units) }}</td>
                                <td>{{ $row->stock_cover_days ? number_format((float)$row->stock_cover_days, 1).' days' : 'N/A' }}</td>
                                <td>{{ number_format((int)$row->near_expiry_base_units) }}</td>
                                <td>{{ number_format((float)$row->gross_profit, 2) }}</td>
                                <td>{{ str($row->recommendation_type)->replace('_', ' ')->title() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">No intelligence data found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const demandLabels = @json($demandLabels);
    const demandValues = @json($demandValues);

    const profitLabels = @json($profitLabels);
    const profitValues = @json($profitValues);

    const priorityLabels = @json($priorityLabels);
    const priorityValues = @json($priorityValues);

    const riskLabels = @json(array_keys($riskData));
    const riskValues = @json(array_values($riskData));

    const tooltipConfig = {
        backgroundColor: '#fff',
        borderColor: 'rgba(15,23,42,.12)',
        borderWidth: 1,
        titleColor: '#0f172a',
        bodyColor: '#64748b',
        padding: 12,
        cornerRadius: 14
    };

    const gridColor = 'rgba(15,23,42,.05)';

    function makeBarChart(id, labels, values, label, color) {
        const ctx = document.getElementById(id);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label,
                    data: values,
                    backgroundColor: color,
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: tooltipConfig
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: '#94a3b8' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: '#64748b',
                            callback: function(value) {
                                const text = this.getLabelForValue(value);
                                return text.length > 18 ? text.substring(0,18) + '…' : text;
                            }
                        }
                    }
                }
            }
        });
    }

    makeBarChart('priorityChart', priorityLabels, priorityValues, 'Priority Score', '#2563eb');
    makeBarChart('demandChart', demandLabels, demandValues, 'Demand Units', '#10b981');
    makeBarChart('profitChart', profitLabels, profitValues, 'Gross Profit', '#8b5cf6');

    const riskCtx = document.getElementById('riskChart');
    if (riskCtx) {
        new Chart(riskCtx, {
            type: 'doughnut',
            data: {
                labels: riskLabels,
                datasets: [{
                    data: riskValues,
                    backgroundColor: ['#f43f5e', '#f59e0b', '#10b981'],
                    borderColor: '#ffffff',
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            color: '#64748b',
                            font: { family: 'DM Sans', size: 12, weight: '700' }
                        }
                    },
                    tooltip: tooltipConfig
                }
            }
        });
    }
});
</script>
@endpush