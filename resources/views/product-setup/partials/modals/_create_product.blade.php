<div class="modal fade" id="createProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <form method="POST" action="{{ route('product-setup.products.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Add Product</h5>
                    <small class="text-muted">Product code and barcode are generated automatically.</small>
                </div>

                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
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
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
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
                                <option value="{{ $category->id }}" data-type-id="{{ $category->product_type_id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Base Unit</label>
                        <select name="base_unit_id" class="custom-select select2-modal" data-placeholder="Select base unit" required>
                            <option value=""></option>
                            @foreach($unitOptions as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Product Name</label>
                        <input name="name" class="form-control" placeholder="Example: Paracetamol 500mg Tablet" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Generic Name</label>
                        <input name="generic_name" class="form-control" placeholder="Example: Paracetamol">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Strength</label>
                        <input name="strength" class="form-control" placeholder="Example: 500mg">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Brand</label>
                        <input name="brand" class="form-control" placeholder="Optional">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Status</label>
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" id="create_product_active" name="is_active" value="1" checked>
                            <label class="custom-control-label" for="create_product_active">Active</label>
                        </div>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Optional product notes"></textarea>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="create_requires_expiry" name="requires_expiry" value="1" checked>
                            <label class="custom-control-label" for="create_requires_expiry">Requires expiry tracking</label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="create_requires_prescription" name="requires_prescription" value="1">
                            <label class="custom-control-label" for="create_requires_prescription">Requires prescription</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer ps-mobile-stack">
                <button class="btn btn-light" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Product</button>
            </div>
        </form>
    </div>
</div>