@foreach($modalProducts as $product)
    @php
        $baseProductUnit = $product->units->firstWhere('is_base', true)
            ?: $product->units->sortBy('quantity_in_base_units')->first();

        $baseRetailPrice = $baseProductUnit
            ? $baseProductUnit->prices->firstWhere('price_type', 'retail')
            : null;

        $baseWholesalePrice = $baseProductUnit
            ? $baseProductUnit->prices->firstWhere('price_type', 'wholesale')
            : null;

        $retailBaseAmount = $baseRetailPrice ? (float) $baseRetailPrice->price : 0;
        $wholesaleBaseAmount = $baseWholesalePrice ? (float) $baseWholesalePrice->price : 0;

        $baseQuantity = $baseProductUnit ? max(1, (int) $baseProductUnit->quantity_in_base_units) : 1;

        $retailPricePerBaseUnit = $retailBaseAmount / $baseQuantity;
        $wholesalePricePerBaseUnit = $wholesaleBaseAmount / $baseQuantity;
    @endphp

    {{-- DETAILS MODAL --}}
    <div class="modal fade" id="productDetailsModal{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">{{ $product->name }}</h5>
                        <small class="text-muted">
                            {{ $product->code }}
                            @if($product->barcode)
                                • {{ $product->barcode }}
                            @endif
                        </small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <div class="ps-detail-box">
                                <div class="ps-detail-label">Type</div>
                                <div class="ps-detail-value">{{ $product->productType?->name ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="ps-detail-box">
                                <div class="ps-detail-label">Category</div>
                                <div class="ps-detail-value">{{ $product->category?->name ?: 'No category' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="ps-detail-box">
                                <div class="ps-detail-label">Base Unit</div>
                                <div class="ps-detail-value">{{ $product->baseUnit?->name ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="ps-detail-box">
                                <div class="ps-detail-label">Status</div>
                                <span class="ps-badge {{ $product->is_active ? 'ps-badge-green' : 'ps-badge-gray' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Product Rules</h6>

                        <div class="ps-pill-list">
                            <span class="ps-badge {{ $product->requires_expiry ? 'ps-badge-yellow' : 'ps-badge-gray' }}">
                                {{ $product->requires_expiry ? 'Requires Expiry' : 'No Expiry Required' }}
                            </span>

                            <span class="ps-badge {{ $product->requires_prescription ? 'ps-badge-red' : 'ps-badge-green' }}">
                                {{ $product->requires_prescription ? 'Requires Prescription' : 'No Prescription Required' }}
                            </span>
                        </div>
                    </div>

                    <hr>

                    <h6 class="font-weight-bold">Package Units</h6>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Conversion</th>
                                    <th>Purchase</th>
                                    <th>Retail</th>
                                    <th>Wholesale</th>
                                    <th>Default</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($product->units as $productUnit)
                                    <tr>
                                        <td>
                                            <strong>{{ $productUnit->unit?->name }}</strong>
                                        </td>

                                        <td>
                                            1 {{ $productUnit->unit?->name }} =
                                            {{ $productUnit->quantity_in_base_units }}
                                            {{ $product->baseUnit?->name }}{{ $productUnit->quantity_in_base_units > 1 ? 's' : '' }}
                                        </td>

                                        <td>{{ $productUnit->can_purchase ? 'Yes' : 'No' }}</td>
                                        <td>{{ $productUnit->can_sell_retail ? 'Yes' : 'No' }}</td>
                                        <td>{{ $productUnit->can_sell_wholesale ? 'Yes' : 'No' }}</td>

                                        <td>
                                            @if($productUnit->is_base)
                                                <span class="ps-badge ps-badge-green">Base</span>
                                            @elseif($productUnit->is_default_sale_unit)
                                                <span class="ps-badge ps-badge-blue">Default Sale</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-muted text-center">
                                            No package units configured.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold">Prices</h6>

                    <div class="ps-alert-info mb-3">
                        Only the base unit price is stored. Other unit prices are calculated from the package conversion.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Conversion</th>
                                    <th>Retail Price</th>
                                    <th>Wholesale Price</th>
                                    <th>Price Source</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($product->units as $productUnit)
                                    @php
                                        $quantity = max(1, (int) $productUnit->quantity_in_base_units);
                                        $calculatedRetail = $retailPricePerBaseUnit * $quantity;
                                        $calculatedWholesale = $wholesalePricePerBaseUnit * $quantity;
                                    @endphp

                                    <tr>
                                        <td>
                                            <strong>{{ $productUnit->unit?->name }}</strong>
                                        </td>

                                        <td>
                                            1 {{ $productUnit->unit?->name }} =
                                            {{ $quantity }}
                                            {{ $product->baseUnit?->name }}{{ $quantity > 1 ? 's' : '' }}
                                        </td>

                                        <td>
                                            {{ number_format($calculatedRetail, 2) }} {{ $baseRetailPrice?->currency ?: 'TZS' }}
                                        </td>

                                        <td>
                                            {{ number_format($calculatedWholesale, 2) }} {{ $baseWholesalePrice?->currency ?: 'TZS' }}
                                        </td>

                                        <td>
                                            @if($baseProductUnit && $productUnit->id === $baseProductUnit->id)
                                                <span class="ps-badge ps-badge-green">Stored Base Price</span>
                                            @else
                                                <span class="ps-badge ps-badge-blue">Calculated</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-muted text-center">
                                            No prices configured.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($product->description)
                        <hr>
                        <div class="text-muted font-weight-bold">{{ $product->description }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- EDIT PRODUCT MODAL --}}
    <div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('product-setup.products.update', $product) }}" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Edit Product</h5>
                        <small class="text-muted">Code and barcode are generated automatically.</small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Product Type</label>
                            <select name="product_type_id"
                                    class="custom-select select2-modal js-type-select"
                                    data-placeholder="Select product type"
                                    required>
                                <option value=""></option>
                                @foreach($typeOptions as $type)
                                    <option value="{{ $type->id }}" {{ $product->product_type_id == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Category</label>
                            <select name="product_category_id"
                                    class="custom-select select2-modal js-category-select"
                                    data-placeholder="Select category">
                                <option value=""></option>
                                @foreach($categoryOptions as $category)
                                    <option value="{{ $category->id }}"
                                            data-type-id="{{ $category->product_type_id }}"
                                            {{ $product->product_category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Base Unit</label>
                            <select name="base_unit_id"
                                    class="custom-select select2-modal"
                                    data-placeholder="Select base unit"
                                    required>
                                <option value=""></option>
                                @foreach($unitOptions as $unit)
                                    <option value="{{ $unit->id }}" {{ $product->base_unit_id == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Product Name</label>
                            <input name="name" class="form-control" value="{{ $product->name }}" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Generated Code</label>
                            <input class="form-control" value="{{ $product->code }}" readonly>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Generated Barcode</label>
                            <input class="form-control" value="{{ $product->barcode }}" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Generic Name</label>
                            <input name="generic_name" class="form-control" value="{{ $product->generic_name }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Strength</label>
                            <input name="strength" class="form-control" value="{{ $product->strength }}">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Brand</label>
                            <input name="brand" class="form-control" value="{{ $product->brand }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $product->description }}</textarea>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="edit_requires_expiry_{{ $product->id }}"
                                       name="requires_expiry"
                                       value="1"
                                       {{ $product->requires_expiry ? 'checked' : '' }}>
                                <label class="custom-control-label" for="edit_requires_expiry_{{ $product->id }}">
                                    Requires expiry
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="edit_requires_prescription_{{ $product->id }}"
                                       name="requires_prescription"
                                       value="1"
                                       {{ $product->requires_prescription ? 'checked' : '' }}>
                                <label class="custom-control-label" for="edit_requires_prescription_{{ $product->id }}">
                                    Requires prescription
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4 mb-2">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="edit_product_active_{{ $product->id }}"
                                       name="is_active"
                                       value="1"
                                       {{ $product->is_active ? 'checked' : '' }}>
                                <label class="custom-control-label" for="edit_product_active_{{ $product->id }}">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer ps-mobile-stack">
                    <button class="btn btn-light" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    {{-- PRODUCT UNITS MODAL --}}
    <div class="modal fade" id="addProductUnitModal{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Package Units - {{ $product->name }}</h5>
                        <small class="text-muted">Base unit: {{ $product->baseUnit?->name }}</small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="ps-alert-info mb-3">
                        Manage package conversions here. Prices will calculate from the base unit price.
                    </div>

                    <h6 class="font-weight-bold mb-2">Existing Units</h6>

                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Unit</th>
                                    <th>Qty In Base Unit</th>
                                    <th>Purchase</th>
                                    <th>Retail</th>
                                    <th>Wholesale</th>
                                    <th>Base</th>
                                    <th>Default Sale</th>
                                    <th>Active</th>
                                    <th>Save</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($product->units as $productUnit)
                                    <tr>
                                        <form method="POST" action="{{ route('product-setup.product-units.update', $productUnit) }}">
                                            @csrf
                                            @method('PUT')

                                            <td>
                                                <strong>{{ $productUnit->unit?->name }}</strong>
                                                <div class="text-muted small">
                                                    1 {{ $productUnit->unit?->name }} =
                                                    {{ $productUnit->quantity_in_base_units }}
                                                    {{ $product->baseUnit?->name }}{{ $productUnit->quantity_in_base_units > 1 ? 's' : '' }}
                                                </div>
                                            </td>

                                            <td style="min-width: 130px;">
                                                <input type="number"
                                                       min="1"
                                                       name="quantity_in_base_units"
                                                       value="{{ $productUnit->quantity_in_base_units }}"
                                                       class="form-control form-control-sm"
                                                       required>
                                            </td>

                                            <td class="text-center">
                                                <input type="checkbox" name="can_purchase" value="1" {{ $productUnit->can_purchase ? 'checked' : '' }}>
                                            </td>

                                            <td class="text-center">
                                                <input type="checkbox" name="can_sell_retail" value="1" {{ $productUnit->can_sell_retail ? 'checked' : '' }}>
                                            </td>

                                            <td class="text-center">
                                                <input type="checkbox" name="can_sell_wholesale" value="1" {{ $productUnit->can_sell_wholesale ? 'checked' : '' }}>
                                            </td>

                                            <td class="text-center">
                                                <input type="checkbox" name="is_base" value="1" {{ $productUnit->is_base ? 'checked' : '' }}>
                                            </td>

                                            <td class="text-center">
                                                <input type="checkbox" name="is_default_sale_unit" value="1" {{ $productUnit->is_default_sale_unit ? 'checked' : '' }}>
                                            </td>

                                            <td class="text-center">
                                                <input type="checkbox" name="is_active" value="1" {{ $productUnit->is_active ? 'checked' : '' }}>
                                            </td>

                                            <td>
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Save
                                                </button>
                                            </td>
                                        </form>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            No units configured yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold mb-2">Add New Unit</h6>

                    <form method="POST" action="{{ route('product-setup.product-units.store') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Unit</label>
                                <select name="unit_id"
                                        class="custom-select select2-modal"
                                        data-placeholder="Select unit"
                                        required>
                                    <option value=""></option>
                                    @foreach($unitOptions as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Quantity In Base Units</label>
                                <input type="number"
                                       min="1"
                                       name="quantity_in_base_units"
                                       class="form-control"
                                       value="1"
                                       required>
                            </div>

                            <div class="col-md-5 mb-3">
                                <label>Allowed Usage</label>

                                <div class="d-flex flex-wrap" style="gap: 12px;">
                                    <label><input type="checkbox" name="can_purchase" value="1" checked> Purchase</label>
                                    <label><input type="checkbox" name="can_sell_retail" value="1" checked> Retail</label>
                                    <label><input type="checkbox" name="can_sell_wholesale" value="1" checked> Wholesale</label>
                                    <label><input type="checkbox" name="is_base" value="1"> Base</label>
                                    <label><input type="checkbox" name="is_default_sale_unit" value="1"> Default Sale</label>
                                    <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary" type="submit">
                            Save Unit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PRICES MODAL - BASE PRICE ONLY --}}
    <div class="modal fade" id="addPriceModal{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">Prices - {{ $product->name }}</h5>
                        <small class="text-muted">
                            Save price once on base unit. Other units are calculated automatically.
                        </small>
                    </div>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @if(! $baseProductUnit)
                        <div class="alert alert-warning mb-0">
                            No base unit is configured for this product. Open Units and mark one unit as Base.
                        </div>
                    @else
                        <div class="ps-alert-info mb-3">
                            Base unit is <strong>{{ $baseProductUnit->unit?->name }}</strong>.
                            Example: if retail is 1,000 per {{ $baseProductUnit->unit?->name }},
                            then a box containing 20 {{ $baseProductUnit->unit?->name }}s becomes 20,000 automatically.
                        </div>

                        <div class="row mb-4">
                            <div class="col-lg-6 mb-3">
                                <div class="ps-detail-box">
                                    <div class="ps-detail-label">Retail Base Price</div>

                                    @if($baseRetailPrice)
                                        <form method="POST" action="{{ route('product-setup.prices.update', $baseRetailPrice) }}" class="d-flex ps-mobile-stack" style="gap: 8px;">
                                            @csrf
                                            @method('PUT')

                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="price"
                                                   value="{{ $baseRetailPrice->price }}"
                                                   class="form-control"
                                                   required>

                                            <input type="hidden" name="currency" value="{{ $baseRetailPrice->currency ?: 'TZS' }}">
                                            <input type="hidden" name="is_active" value="1">

                                            <button class="btn btn-primary" type="submit">
                                                Save
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('product-setup.prices.store') }}" class="d-flex ps-mobile-stack" style="gap: 8px;">
                                            @csrf

                                            <input type="hidden" name="product_unit_id" value="{{ $baseProductUnit->id }}">
                                            <input type="hidden" name="price_type" value="retail">
                                            <input type="hidden" name="currency" value="TZS">
                                            <input type="hidden" name="is_active" value="1">

                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="price"
                                                   class="form-control"
                                                   placeholder="Retail price"
                                                   required>

                                            <button class="btn btn-success" type="submit">
                                                Create
                                            </button>
                                        </form>
                                    @endif

                                    <div class="ps-sub mt-2">
                                        Stored per {{ $baseProductUnit->unit?->name }}.
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 mb-3">
                                <div class="ps-detail-box">
                                    <div class="ps-detail-label">Wholesale Base Price</div>

                                    @if($baseWholesalePrice)
                                        <form method="POST" action="{{ route('product-setup.prices.update', $baseWholesalePrice) }}" class="d-flex ps-mobile-stack" style="gap: 8px;">
                                            @csrf
                                            @method('PUT')

                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="price"
                                                   value="{{ $baseWholesalePrice->price }}"
                                                   class="form-control"
                                                   required>

                                            <input type="hidden" name="currency" value="{{ $baseWholesalePrice->currency ?: 'TZS' }}">
                                            <input type="hidden" name="is_active" value="1">

                                            <button class="btn btn-primary" type="submit">
                                                Save
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('product-setup.prices.store') }}" class="d-flex ps-mobile-stack" style="gap: 8px;">
                                            @csrf

                                            <input type="hidden" name="product_unit_id" value="{{ $baseProductUnit->id }}">
                                            <input type="hidden" name="price_type" value="wholesale">
                                            <input type="hidden" name="currency" value="TZS">
                                            <input type="hidden" name="is_active" value="1">

                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="price"
                                                   class="form-control"
                                                   placeholder="Wholesale price"
                                                   required>

                                            <button class="btn btn-success" type="submit">
                                                Create
                                            </button>
                                        </form>
                                    @endif

                                    <div class="ps-sub mt-2">
                                        Stored per {{ $baseProductUnit->unit?->name }}.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="font-weight-bold mb-2">Calculated Unit Prices</h6>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Unit</th>
                                        <th>Conversion</th>
                                        <th>Retail Price</th>
                                        <th>Wholesale Price</th>
                                        <th>Source</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($product->units as $productUnit)
                                        @php
                                            $quantity = max(1, (int) $productUnit->quantity_in_base_units);
                                            $calculatedRetail = $retailPricePerBaseUnit * $quantity;
                                            $calculatedWholesale = $wholesalePricePerBaseUnit * $quantity;
                                        @endphp

                                        <tr>
                                            <td>
                                                <strong>{{ $productUnit->unit?->name }}</strong>
                                            </td>

                                            <td>
                                                1 {{ $productUnit->unit?->name }} =
                                                {{ $quantity }}
                                                {{ $product->baseUnit?->name }}{{ $quantity > 1 ? 's' : '' }}
                                            </td>

                                            <td>
                                                {{ number_format($calculatedRetail, 2) }} {{ $baseRetailPrice?->currency ?: 'TZS' }}
                                            </td>

                                            <td>
                                                {{ number_format($calculatedWholesale, 2) }} {{ $baseWholesalePrice?->currency ?: 'TZS' }}
                                            </td>

                                            <td>
                                                @if($productUnit->id === $baseProductUnit->id)
                                                    <span class="ps-badge ps-badge-green">Stored</span>
                                                @else
                                                    <span class="ps-badge ps-badge-blue">Auto Calculated</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                No package units configured.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach