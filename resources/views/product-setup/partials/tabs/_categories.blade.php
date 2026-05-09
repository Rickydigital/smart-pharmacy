<div>
    <div class="ps-toolbar">
        <div>
            <h5>Product Categories</h5>
            <p>Sub-groups under product types such as Antibiotic, Perfumes and Test Kits.</p>
        </div>

        @can('product.manage')
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createCategoryModal">
                <i class="mdi mdi-plus mr-1"></i>
                Add Category
            </button>
        @endcan
    </div>

    <div class="ps-table-wrap">
        <table class="table ps-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Type</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($categories as $index => $category)
                    <tr>
                        <td>{{ ($categories->firstItem() ?? 0) + $index }}</td>
                        <td><div class="ps-main-name">{{ $category->name }}</div></td>
                        <td>{{ $category->productType?->name ?: '-' }}</td>
                        <td>{{ $category->code }}</td>
                        <td>{{ $category->description ?: '-' }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td>
                            <span class="ps-badge {{ $category->is_active ? 'ps-badge-green' : 'ps-badge-gray' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            @can('product.manage')
                                <div class="ps-btn-row">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editCategoryModal{{ $category->id }}">
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('product-setup.categories.toggle', $category) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $category->is_active ? 'danger' : 'success' }}">
                                            {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('product-setup.categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">
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
                        <td colspan="8"><div class="ps-empty">No categories found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
        <div class="p-3 border-top">
            {{ $categories->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</div>