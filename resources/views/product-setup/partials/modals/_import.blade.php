<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <form method="POST" action="{{ route('product-setup.import') }}" enctype="multipart/form-data" class="modal-content">
            @csrf

            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="mdi mdi-upload mr-1"></i>
                        Import Product Setup
                    </h5>
                    <small class="text-muted">Upload Excel file using the sample template format.</small>
                </div>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="ps-import-box mb-3">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 12px;">
                        <div>
                            <h6 class="font-weight-bold mb-1">Need the correct format?</h6>
                            <p class="mb-0 text-muted small">
                                Download the sample Excel template, fill products, then upload it here.
                            </p>
                        </div>

                        <a href="{{ route('product-setup.export', ['format' => 'sample']) }}" class="btn btn-outline-primary">
                            <i class="mdi mdi-download mr-1"></i>
                            Download Sample
                        </a>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <label>Excel File</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    <small class="text-muted d-block mt-2">
                        Accepted formats: .xlsx, .xls, .csv
                    </small>
                </div>
            </div>

            <div class="modal-footer ps-mobile-stack">
                <button type="button" class="btn btn-light" data-dismiss="modal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-primary">
                    <i class="mdi mdi-upload mr-1"></i>
                    Import Excel
                </button>
            </div>
        </form>
    </div>
</div>