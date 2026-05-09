<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Product Price List</title>

    <style>
        @page {
            margin: 28px 28px 34px 28px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #0f172a;
            margin: 0;
            background: #ffffff;
        }

        .document {
            width: 100%;
        }

        .header {
            border: 1px solid #dbeafe;
            background: #eff6ff;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            vertical-align: top;
            width: 70%;
        }

        .header-right {
            vertical-align: top;
            width: 30%;
            text-align: right;
        }

        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0 0 4px 0;
            line-height: 1.2;
        }

        .pharmacy-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 2px;
        }

        .small-muted {
            color: #64748b;
            font-size: 10px;
            line-height: 1.35;
        }

        .summary-box {
            display: inline-block;
            border: 1px solid #bfdbfe;
            background: #ffffff;
            border-radius: 8px;
            padding: 8px 10px;
            text-align: left;
        }

        .summary-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: .04em;
        }

        .summary-value {
            font-size: 12px;
            color: #0f172a;
            font-weight: bold;
            margin-top: 2px;
        }

        .type-block {
            margin-top: 16px;
            page-break-inside: avoid;
        }

        .type-header {
            background: #1e3a8a;
            color: #ffffff;
            font-size: 13px;
            font-weight: bold;
            padding: 8px 10px;
            border-radius: 8px 8px 0 0;
            letter-spacing: .02em;
        }

        .category-block {
            margin-bottom: 12px;
            page-break-inside: avoid;
            border: 1px solid #e2e8f0;
            border-top: 0;
            border-radius: 0 0 8px 8px;
            overflow: hidden;
        }

        .category-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 7px 10px;
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }

        .category-header span {
            color: #2563eb;
        }

        table.price-table {
            width: 100%;
            border-collapse: collapse;
        }

        .price-table thead th {
            background: #f1f5f9;
            color: #475569;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 7px 8px;
            border-bottom: 1px solid #cbd5e1;
            text-align: left;
        }

        .price-table tbody td {
            padding: 7px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .price-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .col-no {
            width: 34px;
            text-align: center;
            color: #64748b;
        }

        .col-product {
            width: 48%;
        }

        .col-unit {
            width: 16%;
        }

        .col-price {
            width: 18%;
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
        }

        .product-name {
            font-weight: bold;
            color: #0f172a;
            line-height: 1.25;
        }

        .product-code {
            color: #64748b;
            font-size: 9px;
            margin-top: 2px;
        }

        .unit-pill {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 9px;
        }

        .price-retail {
            color: #1d4ed8;
        }

        .price-wholesale {
            color: #15803d;
        }

        .empty {
            padding: 18px;
            text-align: center;
            color: #64748b;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 14px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            color: #64748b;
            font-size: 9px;
            line-height: 1.4;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
<div class="document">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <h1 class="report-title">Product Price List</h1>

                    <div class="pharmacy-name">
                        {{ $pharmacy->name ?? 'Pharmacy' }}
                    </div>

                    <div class="small-muted">
                        Base unit price report grouped by product type and category.
                        <br>
                        Only base unit prices are stored. Package unit prices are calculated from unit conversion.
                    </div>
                </td>

                <td class="header-right">
                    <div class="summary-box">
                        <div class="summary-label">Generated</div>
                        <div class="summary-value">{{ $generatedAt->format('d M Y') }}</div>
                        <div class="small-muted">{{ $generatedAt->format('h:i A') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @forelse($groupedProducts as $typeName => $categories)
        <div class="type-block">
            <div class="type-header">
                Product Type: {{ $typeName }}
            </div>

            @foreach($categories as $categoryName => $products)
                <div class="category-block">
                    <div class="category-header">
                        Category: <span>{{ $categoryName }}</span>
                    </div>

                    <table class="price-table">
                        <thead>
                            <tr>
                                <th class="col-no">#</th>
                                <th class="col-product">Product Name</th>
                                <th class="col-unit">Base Unit</th>
                                <th class="col-price">Retail Price</th>
                                <th class="col-price">Wholesale Price</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($products as $index => $product)
                                @php
                                    $baseProductUnit = $product->units->firstWhere('is_base', true)
                                        ?: $product->units->sortBy('quantity_in_base_units')->first();

                                    $retailPrice = $baseProductUnit
                                        ? $baseProductUnit->prices->firstWhere('price_type', 'retail')
                                        : null;

                                    $wholesalePrice = $baseProductUnit
                                        ? $baseProductUnit->prices->firstWhere('price_type', 'wholesale')
                                        : null;
                                @endphp

                                <tr>
                                    <td class="col-no">{{ $index + 1 }}</td>

                                    <td class="col-product">
                                        <div class="product-name">{{ $product->name }}</div>

                                        <div class="product-code">
                                            {{ $product->code }}
                                            @if($product->strength)
                                                - {{ $product->strength }}
                                            @endif
                                            @if($product->generic_name)
                                                - {{ $product->generic_name }}
                                            @endif
                                        </div>
                                    </td>

                                    <td class="col-unit">
                                        <span class="unit-pill">
                                            {{ $baseProductUnit?->unit?->name ?: $product->baseUnit?->name ?: '-' }}
                                        </span>
                                    </td>

                                    <td class="col-price price-retail">
                                        {{ $retailPrice ? number_format((float) $retailPrice->price, 2) . ' ' . $retailPrice->currency : '-' }}
                                    </td>

                                    <td class="col-price price-wholesale">
                                        {{ $wholesalePrice ? number_format((float) $wholesalePrice->price, 2) . ' ' . $wholesalePrice->currency : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @empty
        <div class="empty">
            No active products found.
        </div>
    @endforelse

    <div class="footer-note">
        This report displays base unit prices only. Retail and wholesale prices for package units such as Strip, Box, Pack, or Carton are calculated automatically using package conversion.
    </div>
</div>
</body>
</html>