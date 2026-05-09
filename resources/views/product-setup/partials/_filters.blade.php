<div class="card ps-filter-card ps-filter">
    <div class="card-body">
        <form method="GET" action="{{ route('product-setup.index') }}">
            <input type="hidden" name="tab" value="products">

            <div class="row align-items-end">
                <div class="col-lg-4 col-md-12 mb-3">
                    <label>Search</label>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Search product, generic, brand, code or barcode">
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <label>Type</label>
                    <select name="product_type_id"
                            class="custom-select select2-clear js-type-filter"
                            data-placeholder="All types">
                        <option value="">All types</option>
                        @foreach($typeOptions as $type)
                            <option value="{{ $type->id }}" {{ request('product_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 mb-3">
                    <label>Category</label>
                    <select name="product_category_id"
                            class="custom-select select2-clear js-category-filter"
                            data-placeholder="All categories">
                        <option value="">All categories</option>
                        @foreach($categoryOptions as $category)
                            <option value="{{ $category->id }}"
                                    data-type-id="{{ $category->product_type_id }}"
                                    {{ request('product_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 mb-3">
                    <label>Status</label>
                    <select name="status" class="custom-select select2-clear" data-placeholder="All">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-end ps-filter-actions" style="gap: 8px;">
                        @if(request()->hasAny(['search', 'product_type_id', 'product_category_id', 'status']))
                            <a href="{{ route('product-setup.index', ['tab' => 'products']) }}" class="btn btn-light">
                                <i class="mdi mdi-close mr-1"></i>
                                Clear
                            </a>
                        @endif

                        <button class="btn btn-primary" type="submit">
                            <i class="mdi mdi-magnify mr-1"></i>
                            Apply Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>