<div class="modal fade" id="createUnitModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <form method="POST" action="{{ route('product-setup.units.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title">Add Unit</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <div class="modal-body">
                <div class="ps-alert-info mb-3">
                    Unit code will be generated automatically from the unit name.
                </div>

                <div class="form-group">
                    <label>Name</label>
                    <input name="name" class="form-control" placeholder="Example: Tablet, Strip, Box" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <input name="description" class="form-control" placeholder="Short description">
                </div>

                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="unit_active" name="is_active" value="1" checked>
                    <label class="custom-control-label" for="unit_active">Active</label>
                </div>
            </div>

            <div class="modal-footer ps-mobile-stack">
                <button class="btn btn-light" type="button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Unit</button>
            </div>
        </form>
    </div>
</div>