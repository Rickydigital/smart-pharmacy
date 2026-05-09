<div class="card ps-header-card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between ps-header-main" style="gap: 16px;">
            <div class="d-flex align-items-center ps-header-title-wrap" style="gap: 13px;">
                <span class="ps-header-icon">
                    <i class="mdi mdi-package-variant-closed"></i>
                </span>

                <div>
                    <h4 class="ps-hero-title">Product Setup</h4>
                    <p class="ps-hero-text">
                        Manage catalog, package units, retail prices, wholesale prices and product setup rules.
                    </p>
                </div>
            </div>

            @can('product.manage')
                <div class="ps-actions">
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#importModal">
                        <i class="mdi mdi-upload mr-1"></i>
                        <span>Import</span>
                    </button>

                    <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#exportModal">
                        <i class="mdi mdi-download mr-1"></i>
                        <span>Export</span>
                    </button>

                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createProductModal">
                        <i class="mdi mdi-plus mr-1"></i>
                        <span>Product</span>
                    </button>
                </div>
            @endcan
        </div>
    </div>
</div>