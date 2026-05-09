@foreach($units as $unit)
    <div class="modal fade" id="editUnitModal{{ $unit->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <form method="POST" action="{{ route('product-setup.units.update', $unit) }}" class="modal-content">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Unit</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input name="name" class="form-control" value="{{ $unit->name }}" required>
                    </div>

                    <div class="form-group">
                        <label>Generated Code</label>
                        <input class="form-control" value="{{ $unit->code }}" readonly>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <input name="description" class="form-control" value="{{ $unit->description }}">
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="unit_active_{{ $unit->id }}" name="is_active" value="1" {{ $unit->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="unit_active_{{ $unit->id }}">Active</label>
                    </div>
                </div>

                <div class="modal-footer ps-mobile-stack">
                    <button class="btn btn-light" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Update Unit</button>
                </div>
            </form>
        </div>
    </div>
@endforeach