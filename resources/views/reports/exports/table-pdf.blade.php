<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ str_replace('Stock Report', 'Inventory Report', $title) }}</title>

   <style>
@page{
    margin:95px 15px 30px 15px;
}

body{
    font-family:DejaVu Sans,sans-serif;
    font-size:8px;
    color:#0f172a;
}

header{
    position:fixed;
    top:-82px;
    left:0;
    right:0;
    height:70px;
    border-bottom:2px solid #2563eb;
}

footer{
    position:fixed;
    bottom:-22px;
    left:0;
    right:0;
    height:16px;
    border-top:1px solid #e2e8f0;
    color:#64748b;
    font-size:7px;
}

.page-number:after{
    content:counter(page);
}

table{
    width:100%;
    border-collapse:collapse;
}

.top-table td{
    border:none;
    vertical-align:top;
}

.logo-box{
    width:36px;
    height:36px;
    border:1px solid #dbeafe;
    background:#eff6ff;
    border-radius:6px;
    overflow:hidden;
    text-align:center;
}

.logo-box img{
    max-width:32px;
    max-height:32px;
    margin-top:2px;
}

.logo-fallback{
    font-size:11px;
    font-weight:bold;
    color:#2563eb;
    padding-top:10px;
}

.pharmacy-name{
    font-size:12px;
    font-weight:bold;
    color:#0f172a;
}

.pharmacy-info{
    font-size:6.5px;
    color:#64748b;
    line-height:1.3;
}

.report-title{
    text-align:right;
    font-size:12px;
    font-weight:bold;
    color:#2563eb;
}

.report-subtitle{
    text-align:right;
    font-size:6.5px;
    color:#64748b;
}

.meta{
    margin-top:4px;
}

.meta td{
    padding:3px;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    font-size:6.5px;
}

.summary-wrap{
    margin-bottom:6px;
    padding:5px;
    border:1px solid #dbeafe;
    background:#f8fbff;
}

.summary-title{
    font-size:8px;
    font-weight:bold;
    margin-bottom:4px;
}

.summary-table td{
    padding:3px;
    border:1px solid #e2e8f0;
}

.summary-key{
    font-weight:bold;
    background:#eff6ff;
}

.table-title{
    margin:4px 0;
    font-size:8px;
    font-weight:bold;
}

table.report{
    width:100%;
    table-layout:auto;
    border-collapse:collapse;
}

table.report thead{
    display:table-header-group;
}

table.report th{
    background:#2563eb;
    color:white;
    border:1px solid #bfdbfe;
    padding:5px;
    font-size:7px;
    text-align:left;
}

table.report td{
    border:1px solid #e2e8f0;
    padding:4px;
    font-size:7px;
    line-height:1.2;
    vertical-align:middle;
}

table.report tbody tr:nth-child(even) td{
    background:#f8fafc;
}

.status{
    padding:2px 4px;
    border-radius:10px;
    font-size:6px;
    font-weight:bold;
}

.status-available{
    background:#dcfce7;
    color:#166534;
}

.status-expired{
    background:#fee2e2;
    color:#991b1b;
}

.empty{
    padding:15px;
    border:1px dashed #cbd5e1;
    text-align:center;
}
</style>
</head>

<body>
@php
    $pharmacy = \App\Models\Pharmacy::query()->with('mainBranch')->first();
    $branch = $pharmacy?->mainBranch;
    $logoPath = $pharmacy?->logo_path ? public_path('storage/' . $pharmacy->logo_path) : null;

    $displayTitle = str_replace('Stock Report', 'Inventory Report', $title);
    $displaySubtitle = str_replace('stock', 'inventory', $subtitle);

    $statusWords = [
        'available', 'paid', 'completed', 'approved', 'received',
        'expired', 'cancelled', 'rejected', 'voided',
        'depleted', 'pending', 'draft',
        'blocked', 'dispatched',
    ];
@endphp

<header>
    <table class="top-table">
        <tr>
            <td style="width: 52px;">
                <div class="logo-box">
                    @if($logoPath && file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Logo">
                    @else
                        <div class="logo-fallback">{{ strtoupper(substr($pharmacy?->name ?? 'SP', 0, 2)) }}</div>
                    @endif
                </div>
            </td>

            <td>
                <div class="pharmacy-name">{{ $pharmacy?->name ?? 'Smart Pharmacy' }}</div>
                <div class="pharmacy-info">
                    Code: {{ $pharmacy?->code ?? '-' }}<br>
                    Phone: {{ $pharmacy?->phone ?? $branch?->phone ?? '-' }}
                    @if($pharmacy?->email)
                        | Email: {{ $pharmacy->email }}
                    @endif
                    <br>
                    Address: {{ $pharmacy?->address ?? $branch?->address ?? '-' }}
                </div>
            </td>

            <td style="width: 36%;">
                <div class="report-title">{{ $displayTitle }}</div>
                <div class="report-subtitle">{{ $displaySubtitle }}</div>
            </td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td><strong>Branch:</strong> {{ $meta['branch'] ?? 'All branches' }}</td>
            <td><strong>From:</strong> {{ $meta['date_from'] ?? '-' }}</td>
            <td><strong>To:</strong> {{ $meta['date_to'] ?? '-' }}</td>
            <td><strong>Status:</strong> {{ $meta['status'] ?? '-' }}</td>
            <td><strong>Generated:</strong> {{ $generatedAt->format('d M Y h:i A') }}</td>
        </tr>
    </table>
</header>

<footer>
    <div class="footer-left">
        {{ $pharmacy?->name ?? 'Smart Pharmacy' }} | System generated report
    </div>
    <div class="footer-right">
        Page <span class="page-number"></span>
    </div>
</footer>

@if(!empty($summary ?? []))
    <div class="summary-wrap">
        <div class="summary-title">Report Summary</div>

        <table class="summary-table">
            @foreach(array_chunk($summary, 2, true) as $chunk)
                <tr>
                    @foreach($chunk as $key => $value)
                        <td class="summary-key">{{ str_replace('_', ' ', ucfirst($key)) }}</td>
                        <td class="summary-value">{{ $value }}</td>
                    @endforeach

                    @if(count($chunk) === 1)
                        <td class="summary-key"></td>
                        <td class="summary-value"></td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>
@endif

@if(count($rows))
    <div class="table-title">Detailed Records</div>

    <table class="report">
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        @php
                            $cellText = trim((string) $cell);
                            $normalized = strtolower($cellText);
                        @endphp

                        <td>
                            @if(in_array($normalized, $statusWords, true))
                                <span class="status status-{{ $normalized }}">{{ strtoupper($cellText) }}</span>
                            @else
                                {!! nl2br(e($cellText)) !!}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="empty">
        No records found for selected filters.
    </div>
@endif

</body>
</html>