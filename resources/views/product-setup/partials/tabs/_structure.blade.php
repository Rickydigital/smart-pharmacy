<div>
    <div class="ps-toolbar">
        <div>
            <h5>Package Structure</h5>
            <p>Controls conversion such as 1 Strip = 10 Tablets and 1 Box = 100 Tablets.</p>
        </div>
    </div>

    <div class="ps-table-wrap">
        <table class="table ps-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Base Unit</th>
                    <th>Configured Units</th>
                    <th>Purchase</th>
                    <th>Retail</th>
                    <th>Wholesale</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($structureProducts as $index => $product)
                    <tr>
                        <td>{{ ($structureProducts->firstItem() ?? 0) + $index }}</td>
                        <td>
                            <div class="ps-main-name">{{ $product->name }}</div>
                            <div class="ps-sub">{{ $product->code }}</div>
                        </td>
                        <td>{{ $product->baseUnit?->name ?: '-' }}</td>
                        <td>
                            <div class="ps-pill-list">
                                @foreach($product->units as $productUnit)
                                    <span class="ps-badge {{ $productUnit->is_base ? 'ps-badge-green' : 'ps-badge-blue' }}">
                                        1 {{ $productUnit->unit?->name }} =
                                        {{ $productUnit->quantity_in_base_units }}
                                        {{ $product->baseUnit?->name }}{{ $productUnit->quantity_in_base_units > 1 ? 's' : '' }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>{{ $product->units->where('can_purchase', true)->count() }}</td>
                        <td>{{ $product->units->where('can_sell_retail', true)->count() }}</td>
                        <td>{{ $product->units->where('can_sell_wholesale', true)->count() }}</td>
                        <td class="text-right">
                            @can('product.manage')
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addProductUnitModal{{ $product->id }}">
                                    Manage Units
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"><div class="ps-empty">No products found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($structureProducts->hasPages())
        <div class="p-3 border-top">
            {{ $structureProducts->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</div>