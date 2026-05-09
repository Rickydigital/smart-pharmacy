<div>
    <div class="ps-toolbar">
        <div>
            <h5>Product Types</h5>
            <p>Main groups like Medicine, Cosmetic, Medical Device and Personal Care.</p>
        </div>

        @can('product.manage')
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createTypeModal">
                <i class="mdi mdi-plus mr-1"></i>
                Add Type
            </button>
        @endcan
    </div>

    <div class="ps-table-wrap">
        <table class="table ps-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Categories</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($productTypes as $index => $type)
                    <tr>
                        <td>{{ ($productTypes->firstItem() ?? 0) + $index }}</td>
                        <td><div class="ps-main-name">{{ $type->name }}</div></td>
                        <td>{{ $type->code }}</td>
                        <td>{{ $type->description ?: '-' }}</td>
                        <td>{{ $type->categories_count }}</td>
                        <td>{{ $type->products_count }}</td>
                        <td>
                            <span class="ps-badge {{ $type->is_active ? 'ps-badge-green' : 'ps-badge-gray' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            @can('product.manage')
                                <div class="ps-btn-row">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editTypeModal{{ $type->id }}">
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('product-setup.types.toggle', $type) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $type->is_active ? 'danger' : 'success' }}">
                                            {{ $type->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('product-setup.types.destroy', $type) }}" class="d-inline" onsubmit="return confirm('Delete this product type?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8"><div class="ps-empty">No product types found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($productTypes->hasPages())
        <div class="p-3 border-top">
            {{ $productTypes->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</div>