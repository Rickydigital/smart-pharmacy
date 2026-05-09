@foreach($categories as $category)
    <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('product-setup.categories.update', $category) }}" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Product Category</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Product Type</label>
                        <select name="product_type_id" class="custom-select select2-modal" data-placeholder="Select product type" required>
                            <option value=""></option>
                            @foreach($typeOptions as $type)
                                <option value="{{ $type->id }}" {{ $category->product_type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Name</label>
                        <input name="name" class="form-control" value="{{ $category->name }}" required>
                    </div>

                    <div class="form-group">
                        <label>Generated Code</label>
                        <input class="form-control" value="{{ $category->code }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <input name="description" class="form-control" value="{{ $category->description }}">
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="cat_active_{{ $category->id }}" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="cat_active_{{ $category->id }}">Active</label>
                    </div>
                </div>

                <div class="modal-footer ps-mobile-stack">
                    <button class="btn btn-light" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Update Category</button>
                </div>
            </form>
        </div>
    </div>
@endforeach