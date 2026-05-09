<div>
    <div class="ps-toolbar">
        <div>
            <h5>Units</h5>
            <p>Reusable units like Pill, Tablet, Strip, Box, Bottle, Piece and Pack.</p>
        </div>

        @can('product.manage')
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createUnitModal">
                <i class="mdi mdi-plus mr-1"></i>
                Add Unit
            </button>
        @endcan
    </div>

    <div class="ps-table-wrap">
        <table class="table ps-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Unit</th>
                    <th>Code</th>
                    <th>Description</th>
                    <th>Used In</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($units as $index => $unit)
                    <tr>
                        <td>{{ ($units->firstItem() ?? 0) + $index }}</td>
                        <td><div class="ps-main-name">{{ $unit->name }}</div></td>
                        <td>{{ $unit->code }}</td>
                        <td>{{ $unit->description ?: '-' }}</td>
                        <td>{{ $unit->product_units_count }} products</td>
                        <td>
                            <span class="ps-badge {{ $unit->is_active ? 'ps-badge-green' : 'ps-badge-gray' }}">
                                {{ $unit->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right">
                            @can('product.manage')
                                <div class="ps-btn-row">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editUnitModal{{ $unit->id }}">
                                        Edit
                                    </button>

                                    <form method="POST" action="{{ route('product-setup.units.toggle', $unit) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-{{ $unit->is_active ? 'danger' : 'success' }}">
                                            {{ $unit->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('product-setup.units.destroy', $unit) }}" class="d-inline" onsubmit="return confirm('Delete this unit?')">
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
                        <td colspan="7"><div class="ps-empty">No units found.</div></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($units->hasPages())
        <div class="p-3 border-top">
            {{ $units->links('vendor.pagination.bootstrap-5') }}
        </div>
    @endif
</div>