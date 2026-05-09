<div>
    <div class="ps-toolbar">
        <div>
            <h5>Prices</h5>
            <p>Set retail and wholesale prices per product unit.</p>
        </div>
    </div>

    <div class="ps-table-wrap">
        <table class="table ps-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit</th>
                    <th>Allowed Sale</th>
                    <th>Retail Price</th>
                    <th>Wholesale Price</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($priceProducts as $product)
                    @forelse($product->units as $productUnit)
                        @php
                            $retailPrice = $productUnit->prices->firstWhere('price_type', 'retail');
                            $wholesalePrice = $productUnit->prices->firstWhere('price_type', 'wholesale');
                        @endphp

                        <tr>
                            <td>
                                <div class="ps-main-name">{{ $product->name }}</div>
                                <div class="ps-sub">{{ $product->code }}</div>
                            </td>

                            <td>
                                {{ $productUnit->unit?->name }}
                                <div class="ps-sub">
                                    1 {{ $productUnit->unit?->name }} =
                                    {{ $productUnit->quantity_in_base_units }}
                                    {{ $product->baseUnit?->name }}{{ $productUnit->quantity_in_base_units > 1 ? 's' : '' }}
                                </div>
                            </td>

                            <td>
                                <div class="ps-pill-list">
                                    @if($productUnit->can_sell_retail)
                                        <span class="ps-badge ps-badge-blue">Retail</span>
                                    @endif

                                    @if($productUnit->can_sell_wholesale)
                                        <span class="ps-badge ps-badge-green">Wholesale</span>
                                    @endif
                                </div>
                            </td>

                            <td>{{ $retailPrice ? number_format($retailPrice->price, 2) . ' ' . $retailPrice->currency : '-' }}</td>
                            <td>{{ $wholesalePrice ? number_format($wholesalePrice->price, 2) . ' ' . $wholesalePrice->currency : '-' }}</td>

                            <td class="text-right">
                                @can('product.manage')
                                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addPriceModal{{ $product->id }}">
                                        Manage Prices
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="ps-empty">No units configured for {{ $product->name }}.</div>
                            </td>
                        </tr>
                    @endforelse
                @empty
                    <tr>
                        <td colspan="6"><div class="ps-empty">No products found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($priceProducts->hasPages())
        <div class="p-3 border-top">
            {{ $priceProducts->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</div>