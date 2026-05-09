<div>
    <div class="ps-toolbar">
        <div>
            <h5>Products</h5>
            <p>All medicines, cosmetics, devices, personal care and general pharmacy items.</p>
        </div>
    </div>

    @include('product-setup.partials._filters')

    <div class="ps-table-wrap">
        <table class="table ps-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Type / Category</th>
                    <th>Base Unit</th>
                    <th>Rules</th>
                    <th>Status</th>
                    <th>Package</th>
                    <th>Prices</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($products as $index => $product)
                    <tr>
                        <td>{{ ($products->firstItem() ?? 0) + $index }}</td>

                        <td>
                            <div class="ps-main-name">{{ $product->name }}</div>
                            <div class="ps-sub">
                                {{ $product->code }}
                                @if($product->barcode)
                                    • {{ $product->barcode }}
                                @endif
                                @if($product->strength)
                                    • {{ $product->strength }}
                                @endif
                                @if($product->generic_name)
                                    • {{ $product->generic_name }}
                                @endif
                            </div>
                        </td>

                        <td>
                            <span class="ps-badge ps-badge-blue">{{ $product->productType?->name ?: '-' }}</span>
                            <div class="ps-sub">{{ $product->category?->name ?: 'No category' }}</div>
                        </td>

                        <td>{{ $product->baseUnit?->name ?: '-' }}</td>

                        <td>
                            <div class="ps-pill-list">
                                @if($product->requires_expiry)
                                    <span class="ps-badge ps-badge-yellow">Expiry</span>
                                @endif

                                @if($product->requires_prescription)
                                    <span class="ps-badge ps-badge-red">Prescription</span>
                                @endif

                                @if(!$product->requires_expiry && !$product->requires_prescription)
                                    <span class="ps-badge ps-badge-gray">Normal</span>
                                @endif
                            </div>
                        </td>

                        <td>
                            <span class="ps-badge {{ $product->is_active ? 'ps-badge-green' : 'ps-badge-gray' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td>
                            <span class="ps-badge ps-badge-blue">{{ $product->units->count() }} units</span>
                        </td>

                        <td>
                            <span class="ps-badge ps-badge-green">{{ $product->prices->count() }} prices</span>
                        </td>

                        <td class="text-right">
                            <div class="ps-btn-row">
                                <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#productDetailsModal{{ $product->id }}">
                                    View
                                </button>

                                @can('product.manage')
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editProductModal{{ $product->id }}">
                                        Edit
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#addProductUnitModal{{ $product->id }}">
                                        Units
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#addPriceModal{{ $product->id }}">
                                        Prices
                                    </button>

                                    <form method="POST" action="{{ route('product-setup.products.toggle', $product) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-{{ $product->is_active ? 'danger' : 'success' }}" type="submit">
                                            {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="ps-empty">No products found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="p-3 border-top">
            {{ $products->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</div>