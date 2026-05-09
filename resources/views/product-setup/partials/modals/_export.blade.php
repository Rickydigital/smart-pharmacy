<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">
                        <i class="mdi mdi-download mr-1"></i>
                        Export Product Setup
                    </h5>
                    <small class="text-muted">Choose the file you want to download.</small>
                </div>

                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="ps-export-grid">
                    <div class="ps-export-card">
                        <i class="mdi mdi-file-excel-outline"></i>
                        <h6>Excel Export</h6>
                        <p>Download current product setup as Excel.</p>
                        <a href="{{ route('product-setup.export', ['format' => 'excel']) }}" class="btn btn-success btn-sm btn-block">
                            Export Excel
                        </a>
                    </div>

                    <div class="ps-export-card">
                        <i class="mdi mdi-file-pdf-box"></i>
                        <h6>PDF Export</h6>
                        <p>Download printable product setup report.</p>
                        <a href="{{ route('product-setup.export', ['format' => 'pdf']) }}" class="btn btn-danger btn-sm btn-block">
                            Export PDF
                        </a>
                    </div>

                    <div class="ps-export-card">
                        <i class="mdi mdi-table-arrow-down"></i>
                        <h6>Sample Template</h6>
                        <p>Download sample Excel template for import.</p>
                        <a href="{{ route('product-setup.export', ['format' => 'sample']) }}" class="btn btn-primary btn-sm btn-block">
                            Download Sample
                        </a>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>