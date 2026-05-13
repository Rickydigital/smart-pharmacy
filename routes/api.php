<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessCardController;
use App\Http\Controllers\Api\DailyClosingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\InventoryAlertController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\ProductSetupController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Public\PublicProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\QuickSearchController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SalesReturnController;
use App\Http\Controllers\Api\SetupController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TopbarController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {
    Route::get('/', fn () => response()->json([
        'success' => true,
        'message' => 'Smart Pharmacy API',
        'data' => [
            'version' => 'v1',
        ],
    ]))->name('index');

    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.otp.send');
    Route::post('/verify-password-otp', [AuthController::class, 'verifyPasswordOtp'])->name('password.otp.verify');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.otp.reset');
    
    Route::prefix('public')->group(function () {
        Route::get('/branches/{branch}/product-types', [PublicProductController::class, 'types']);
        Route::get('/branches/{branch}/product-categories', [PublicProductController::class, 'categories']);

        Route::get('/branches/{branch}/products', [PublicProductController::class, 'index']);
        Route::get('/branches/{branch}/products/search', [PublicProductController::class, 'search']);
        Route::get('/branches/{branch}/products/{product}/availability', [PublicProductController::class, 'availability']);
    });

    Route::middleware([StartSession::class, 'auth:sanctum', 'smart-control'])->group(function () {
        Route::get('/user', [AuthController::class, 'me'])->name('user');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('logout-all');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');
        

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/quick-search', QuickSearchController::class)->name('quick-search');
        Route::get('/topbar', [TopbarController::class, 'index'])->name('topbar');

        Route::prefix('business-card')->name('business-card.')->group(function () {
            Route::get('/', [BusinessCardController::class, 'profile'])->name('profile');
            Route::get('/download', [BusinessCardController::class, 'download'])->name('download');
            Route::get('/print', [BusinessCardController::class, 'print'])->name('print');
        });

        Route::prefix('setup')->name('setup.')->group(function () {
            Route::get('/', [SetupController::class, 'index'])->name('index');
            Route::put('/pharmacy', [SetupController::class, 'updatePharmacy'])->name('pharmacy.update');
            Route::post('/branches', [SetupController::class, 'storeBranch'])->name('branches.store');
            Route::put('/branches/{branch}', [SetupController::class, 'updateBranch'])->name('branches.update');
            Route::patch('/branches/{branch}/main', [SetupController::class, 'makeMainBranch'])->name('branches.main');
            Route::patch('/branches/{branch}/toggle', [SetupController::class, 'toggleBranch'])->name('branches.toggle');
            Route::put('/settings', [SetupController::class, 'updateSettings'])->name('settings.update');
            Route::post('/pharmacy/logo', [SetupController::class, 'updatePharmacyLogo'])->name('pharmacy.logo');
        });

        Route::prefix('product-setup')->name('product-setup.')->group(function () {
            Route::get('/', [ProductSetupController::class, 'index'])->name('index');
            Route::get('/export', [ProductSetupController::class, 'export'])->name('export');
            Route::post('/import', [ProductSetupController::class, 'import'])->name('import');

            Route::post('/types', [ProductSetupController::class, 'storeType'])->name('types.store');
            Route::put('/types/{productType}', [ProductSetupController::class, 'updateType'])->name('types.update');
            Route::patch('/types/{productType}/toggle', [ProductSetupController::class, 'toggleType'])->name('types.toggle');
            Route::delete('/types/{productType}', [ProductSetupController::class, 'destroyType'])->name('types.destroy');

            Route::post('/categories', [ProductSetupController::class, 'storeCategory'])->name('categories.store');
            Route::put('/categories/{productCategory}', [ProductSetupController::class, 'updateCategory'])->name('categories.update');
            Route::patch('/categories/{productCategory}/toggle', [ProductSetupController::class, 'toggleCategory'])->name('categories.toggle');
            Route::delete('/categories/{productCategory}', [ProductSetupController::class, 'destroyCategory'])->name('categories.destroy');

            Route::post('/units', [ProductSetupController::class, 'storeUnit'])->name('units.store');
            Route::put('/units/{unit}', [ProductSetupController::class, 'updateUnit'])->name('units.update');
            Route::patch('/units/{unit}/toggle', [ProductSetupController::class, 'toggleUnit'])->name('units.toggle');
            Route::delete('/units/{unit}', [ProductSetupController::class, 'destroyUnit'])->name('units.destroy');

            Route::post('/products', [ProductSetupController::class, 'storeProduct'])->name('products.store');
            Route::put('/products/{product}', [ProductSetupController::class, 'updateProduct'])->name('products.update');
            Route::patch('/products/{product}/toggle', [ProductSetupController::class, 'toggleProduct'])->name('products.toggle');
            Route::delete('/products/{product}', [ProductSetupController::class, 'destroyProduct'])->name('products.destroy');

            Route::post('/product-units', [ProductSetupController::class, 'storeProductUnit'])->name('product-units.store');
            Route::put('/product-units/{productUnit}', [ProductSetupController::class, 'updateProductUnit'])->name('product-units.update');
            Route::patch('/product-units/{productUnit}/toggle', [ProductSetupController::class, 'toggleProductUnit'])->name('product-units.toggle');
            Route::patch('/product-units/{productUnit}/default-sale', [ProductSetupController::class, 'makeDefaultSaleUnit'])->name('product-units.default-sale');
            Route::delete('/product-units/{productUnit}', [ProductSetupController::class, 'destroyProductUnit'])->name('product-units.destroy');

            Route::post('/prices', [ProductSetupController::class, 'storePrice'])->name('prices.store');
            Route::put('/prices/{productPrice}', [ProductSetupController::class, 'updatePrice'])->name('prices.update');
            Route::patch('/prices/{productPrice}/toggle', [ProductSetupController::class, 'togglePrice'])->name('prices.toggle');
            Route::delete('/prices/{productPrice}', [ProductSetupController::class, 'destroyPrice'])->name('prices.destroy');
        });

        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('index');
            Route::get('/products/search', [PosController::class, 'searchProducts'])->name('products.search');
            Route::get('/products/{product}', [PosController::class, 'showProduct'])->name('products.show');
            Route::get('/products/{product}/units', [PosController::class, 'productUnits'])->name('products.units');
            Route::get('/day-stats', [PosController::class, 'dayStats'])->name('day-stats');
            Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
            Route::get('/today-sales', [PosController::class, 'todaySales'])->name('today-sales');
            Route::get('/sales/{sale}/receipt', [PosController::class, 'receipt'])->name('sales.receipt');
            Route::get('/expenses', [PosController::class, 'expenses'])->name('expenses.index');
            Route::post('/expenses', [PosController::class, 'storeExpense'])->name('expenses.store');
            Route::delete('/expenses/{expense}', [PosController::class, 'destroyExpense'])->name('expenses.destroy');
        });

        Route::apiResource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::patch('/suppliers/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('suppliers.toggle');

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

        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('index');
            Route::patch('/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('adjust');
            Route::patch('/{inventory}/toggle-block', [InventoryController::class, 'toggleBlock'])->name('toggle-block');
            Route::patch('/{inventory}/mark-expired', [InventoryController::class, 'markExpired'])->name('mark-expired');
        });
        Route::get('/inventory-movements', [InventoryController::class, 'movements'])->name('inventory-movements.index');
        Route::get('/batches', [InventoryController::class, 'batches'])->name('batches.index');
        Route::get('/stock-movements', [InventoryController::class, 'stockMovements'])->name('stock-movements.index');

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

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
            Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
            Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
            Route::get('/expenses', [ReportController::class, 'expenses'])->name('expenses');
            Route::get('/prescriptions', [ReportController::class, 'prescriptions'])->name('prescriptions');
            Route::get('/{report}/export', [ReportController::class, 'export'])
                ->whereIn('report', ['center', 'sales', 'stock', 'purchases', 'profit', 'expenses'])
                ->name('export');
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::patch('/{user}/toggle', [UserController::class, 'toggle'])->name('toggle');
            Route::patch('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        });

        Route::prefix('locations')->group(function () {
            Route::get('/countries', [LocationController::class, 'countries']);
            Route::get('/regions', [LocationController::class, 'regions']);
            Route::get('/districts', [LocationController::class, 'districts']);
            Route::get('/wards', [LocationController::class, 'wards']);
            Route::get('/streets', [LocationController::class, 'streets']);
            Route::get('/streets/{street}', [LocationController::class, 'streetShow']);
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::put('/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('permissions.update');
        });

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::prefix('ai')->name('ai.')->group(function () {
            Route::get('/', [AiController::class, 'index'])->name('index');
            Route::get('/conversations', [AiController::class, 'conversations'])->name('conversations.index');
        });
    });
});
