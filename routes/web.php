<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BusinessCardController;
use App\Http\Controllers\DailyClosingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\Intelligence\IntelligenceController;
use App\Http\Controllers\InventoryAlertController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryMovementController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\Setup\BranchSetupController;
use App\Http\Controllers\Setup\PharmacyProfileController;
use App\Http\Controllers\Setup\PharmacySettingController;
use App\Http\Controllers\ProductSetup\ProductCategoryController;
use App\Http\Controllers\ProductSetup\ProductController;
use App\Http\Controllers\ProductSetup\ProductPriceController;
use App\Http\Controllers\ProductSetup\ProductTypeController;
use App\Http\Controllers\ProductSetup\ProductUnitController;
use App\Http\Controllers\ProductSetup\UnitController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\QuickSearchController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'smart-control'])
    ->name('dashboard');
Route::get('/quick-search', QuickSearchController::class)
    ->middleware(['auth'])
    ->name('quick-search');

Route::middleware(['auth', 'smart-control'])->group(function () {


      Route::get('/business-card/download', [BusinessCardController::class, 'downloadBusinessCardPdf'])
        ->name('business-card.download');

      Route::get('/business-card/print', [BusinessCardController::class, 'printBusinessCardPdf'])
            ->name('business-card.print');

    /*
    |--------------------------------------------------------------------------
    | Setup
    |--------------------------------------------------------------------------
    */
    Route::prefix('setup')->name('setup.')->group(function () {
        Route::get('/', [PharmacyProfileController::class, 'index'])->name('index');
        Route::put('/pharmacy', [PharmacyProfileController::class, 'update'])->name('pharmacy.update');
        Route::post('/branches', [BranchSetupController::class, 'store'])->name('branches.store');
        Route::put('/branches/{branch}', [BranchSetupController::class, 'update'])->name('branches.update');
        Route::patch('/branches/{branch}/main', [BranchSetupController::class, 'makeMain'])->name('branches.main');
        Route::patch('/branches/{branch}/toggle', [BranchSetupController::class, 'toggle'])->name('branches.toggle');
        Route::put('/settings', [PharmacySettingController::class, 'update'])->name('settings.update');
    });

    /*
    |--------------------------------------------------------------------------
    | Smart POS
    |--------------------------------------------------------------------------
    */
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/products/search', [PosController::class, 'searchProducts'])->name('products.search');
        Route::get('/products/{product}/units', [PosController::class, 'productUnits'])->name('products.units');
        Route::get('/day-stats', [PosController::class, 'dayStats'])->name('day-stats');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::get('/today-sales', [PosController::class, 'todaySales'])->name('today-sales');
        Route::get('/sales/{sale}/receipt', [PosController::class, 'receipt'])->name('sales.receipt');
        Route::get('/expenses', [PosController::class, 'expenses'])->name('expenses.index');
        Route::post('/expenses', [PosController::class, 'storeExpense'])->name('expenses.store');
        Route::delete('/expenses/{expense}', [PosController::class, 'destroyExpense'])->name('expenses.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', fn() => view('table'))->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | Product Setup
    |--------------------------------------------------------------------------
    */
    Route::prefix('product-setup')->name('product-setup.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/export', [ProductController::class, 'export'])->name('export');
        Route::post('/import', [ProductController::class, 'import'])->name('import');

        Route::prefix('types')->name('types.')->group(function () {
            Route::post('/', [ProductTypeController::class, 'store'])->name('store');
            Route::put('/{productType}', [ProductTypeController::class, 'update'])->name('update');
            Route::patch('/{productType}/toggle', [ProductTypeController::class, 'toggle'])->name('toggle');
            Route::delete('/{productType}', [ProductTypeController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::post('/', [ProductCategoryController::class, 'store'])->name('store');
            Route::put('/{productCategory}', [ProductCategoryController::class, 'update'])->name('update');
            Route::patch('/{productCategory}/toggle', [ProductCategoryController::class, 'toggle'])->name('toggle');
            Route::delete('/{productCategory}', [ProductCategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('units')->name('units.')->group(function () {
            Route::post('/', [UnitController::class, 'store'])->name('store');
            Route::put('/{unit}', [UnitController::class, 'update'])->name('update');
            Route::patch('/{unit}/toggle', [UnitController::class, 'toggle'])->name('toggle');
            Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::patch('/{product}/toggle', [ProductController::class, 'toggle'])->name('toggle');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('product-units')->name('product-units.')->group(function () {
            Route::post('/', [ProductUnitController::class, 'store'])->name('store');
            Route::put('/{productUnit}', [ProductUnitController::class, 'update'])->name('update');
            Route::patch('/{productUnit}/toggle', [ProductUnitController::class, 'toggle'])->name('toggle');
            Route::patch('/{productUnit}/default-sale', [ProductUnitController::class, 'makeDefaultSaleUnit'])->name('default-sale');
            Route::delete('/{productUnit}', [ProductUnitController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('prices')->name('prices.')->group(function () {
            Route::post('/', [ProductPriceController::class, 'store'])->name('store');
            Route::put('/{productPrice}', [ProductPriceController::class, 'update'])->name('update');
            Route::patch('/{productPrice}/toggle', [ProductPriceController::class, 'toggle'])->name('toggle');
            Route::delete('/{productPrice}', [ProductPriceController::class, 'destroy'])->name('destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Suppliers & Purchases
    |--------------------------------------------------------------------------
    */
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::patch('/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('toggle');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
        Route::put('/{purchase}', [PurchaseController::class, 'update'])->name('update');

        Route::post('/{purchase}/items', [PurchaseController::class, 'storeItem'])->name('items.store');
        Route::put('/items/{purchaseItem}', [PurchaseController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{purchaseItem}', [PurchaseController::class, 'destroyItem'])->name('items.destroy');

        Route::patch('/{purchase}/receive', [PurchaseController::class, 'receive'])->name('receive');
        Route::patch('/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('cancel');
        Route::delete('/{purchase}', [PurchaseController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('supplier-payments')->name('supplier-payments.')->group(function () {
        Route::get('/', fn() => view('dashboard'))->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | Inventory
    |--------------------------------------------------------------------------
    */
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::patch('/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
        Route::patch('/{inventory}/toggle-block', [InventoryController::class, 'toggleBlock'])->name('toggle-block');
        Route::patch('/{inventory}/mark-expired', [InventoryController::class, 'markExpired'])->name('mark-expired');
    });

    Route::get('/inventory-movements', [InventoryMovementController::class, 'index'])->name('inventory-movements.index');

    Route::prefix('batches')->name('batches.')->group(function () {
        Route::get('/', fn() => view('dashboard'))->name('index');
    });

    Route::prefix('stock-movements')->name('stock-movements.')->group(function () {
        Route::get('/', fn() => view('dashboard'))->name('index');
    });

    Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function () {
        Route::get('/', [StockAdjustmentController::class, 'index'])->name('index');
        Route::get('/search-inventory', [StockAdjustmentController::class, 'searchInventory'])->name('search-inventory');
        Route::post('/', [StockAdjustmentController::class, 'store'])->name('store');
        Route::get('/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('show');
        Route::patch('/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])->name('approve');
        Route::patch('/{stockAdjustment}/reject', [StockAdjustmentController::class, 'reject'])->name('reject');
        Route::patch('/{stockAdjustment}/cancel', [StockAdjustmentController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {
        Route::get('/', [StockTransferController::class, 'index'])->name('index');
        Route::get('/search-inventory', [StockTransferController::class, 'searchInventory'])->name('search-inventory');
        Route::post('/', [StockTransferController::class, 'store'])->name('store');
        Route::get('/{stockTransfer}', [StockTransferController::class, 'show'])->name('show');

        Route::patch('/{stockTransfer}/approve', [StockTransferController::class, 'approve'])->name('approve');
        Route::patch('/{stockTransfer}/reject', [StockTransferController::class, 'reject'])->name('reject');
        Route::patch('/{stockTransfer}/cancel', [StockTransferController::class, 'cancel'])->name('cancel');

        Route::patch('/{stockTransfer}/dispatch', [StockTransferController::class, 'dispatch'])->name('dispatch');
        Route::patch('/{stockTransfer}/receive', [StockTransferController::class, 'receive'])->name('receive');
    });

    Route::prefix('inventory-alerts')->name('inventory-alerts.')->group(function () {
        Route::get('/', [InventoryAlertController::class, 'index'])->name('index');
        Route::patch('/{inventoryAlert}/read', [InventoryAlertController::class, 'markRead'])->name('read');
        Route::patch('/{inventoryAlert}/resolve', [InventoryAlertController::class, 'resolve'])->name('resolve');
        Route::patch('/{inventoryAlert}/ignore', [InventoryAlertController::class, 'ignore'])->name('ignore');
        Route::post('/generate', [InventoryAlertController::class, 'generate'])->name('generate');
    });
    
    

    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/receipt', [SaleController::class, 'receipt'])->name('receipt');
        Route::patch('/{sale}/cancel', [SaleController::class, 'cancel'])->name('cancel');
    });

    Route::prefix('sales-returns')->name('sales-returns.')->group(function () {
        Route::get('/', [SalesReturnController::class, 'index'])->name('index');
        Route::get('/search-sale', [SalesReturnController::class, 'searchSale'])->name('search-sale');
        Route::post('/', [SalesReturnController::class, 'store'])->name('store');
        Route::get('/{salesReturn}', [SalesReturnController::class, 'show'])->name('show');
        Route::patch('/{salesReturn}/approve', [SalesReturnController::class, 'approve'])->name('approve');
        Route::patch('/{salesReturn}/reject', [SalesReturnController::class, 'reject'])->name('reject');
        Route::patch('/{salesReturn}/cancel', [SalesReturnController::class, 'cancel'])->name('cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | Prescription
    |--------------------------------------------------------------------------
    */
    Route::prefix('prescriptions')->name('prescriptions.')->group(function () {
        Route::get('/', fn() => view('dashboard'))->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | Expenses & Daily Closing
    |--------------------------------------------------------------------------
    */

    Route::prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');

        Route::post('/categories', [ExpenseController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{expenseCategory}', [ExpenseController::class, 'updateCategory'])->name('categories.update');
        Route::patch('/categories/{expenseCategory}/toggle', [ExpenseController::class, 'toggleCategory'])->name('categories.toggle');

        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::patch('/{expense}/void', [ExpenseController::class, 'void'])->name('void');
    });

    Route::prefix('daily-closings')->name('daily-closings.')->group(function () {
        Route::get('/', [DailyClosingController::class, 'index'])->name('index');
        Route::post('/calculate', [DailyClosingController::class, 'calculate'])->name('calculate');
        Route::post('/', [DailyClosingController::class, 'store'])->name('store');
        Route::patch('/{dailyClosing}/submit', [DailyClosingController::class, 'submit'])->name('submit');
        Route::patch('/{dailyClosing}/verify', [DailyClosingController::class, 'verify'])->name('verify');
        Route::patch('/{dailyClosing}/reject', [DailyClosingController::class, 'reject'])->name('reject');
        Route::patch('/{dailyClosing}/recalculate', [DailyClosingController::class, 'recalculate'])->name('recalculate');
    });

    Route::prefix('intelligence')
    ->name('intelligence.')
    ->group(function () {
        Route::get('/', [IntelligenceController::class, 'index'])
            ->name('index');
    });
    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */
   Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
        Route::get('/prescriptions', [ReportController::class, 'prescriptions'])->name('prescriptions');

        Route::get('/{report}/export', [ReportController::class, 'export'])
            ->whereIn('report', ['center', 'sales', 'stock', 'purchases', 'profit'])
            ->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | AI Assistant
    |--------------------------------------------------------------------------
    */
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::get('/', fn() => view('dashboard'))->name('index');

        Route::prefix('conversations')->name('conversations.')->group(function () {
            Route::get('/', fn() => view('dashboard'))->name('index');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Users, Roles & Activity Logs
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle', [UserController::class, 'toggle'])->name('toggle');
        Route::patch('/{user}/password', [UserController::class, 'resetPassword'])->name('password');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'index'])->name('index');
        Route::get('/{role}', [RolePermissionController::class, 'show'])->name('show');
        Route::put('/{role}/permissions', [RolePermissionController::class, 'updatePermissions'])->name('permissions.update');
    });

    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
