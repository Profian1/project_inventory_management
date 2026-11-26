<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Redirect the root URL to the dashboard (the app lives behind authentication).
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    /*
    | Placeholder pages -------------------------------------------------------
    | These named routes render a shared placeholder view for now. Each will be
    | replaced by a full resource controller in later phases, but the route
    | names stay stable so the sidebar navigation already works.
    */
    // Products module — full resource with extra routes for CSV export,
    // bulk actions and trash management (admin-only restore/force-delete).
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::post('/products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');
    Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete');
    Route::resource('products', ProductController::class);

    // Stock movements module — the transparent stock ledger.
    Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
    Route::get('/stock/create', [StockMovementController::class, 'create'])->name('stock.create');
    Route::post('/stock', [StockMovementController::class, 'store'])->name('stock.store');
    Route::get('/stock/product/{product}', [StockMovementController::class, 'productMovements'])->name('stock.product');

    // AJAX endpoints for the searchable selects (session-authenticated).
    Route::get('/api/products/search', [ProductController::class, 'search'])->name('api.products.search');
    Route::get('/api/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');

    // Sales / point-of-sale module.
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
    Route::get('/sales/{sale}/receipt', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');

    // Customers module — full CRUD.
    Route::resource('customers', CustomerController::class);

    // Debts module — credit tracking + aging report.
    Route::get('/debts/aging', [DebtController::class, 'aging'])->name('debts.aging');
    Route::get('/debts/aging/export', [DebtController::class, 'exportAging'])->name('debts.aging.export');
    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
    Route::get('/debts/{debt}', [DebtController::class, 'show'])->name('debts.show');
    Route::post('/debts/{debt}/payments', [DebtController::class, 'storePayment'])->name('debts.payments.store');

    // Suppliers module — lightweight CRUD + purchases report.
    Route::get('/suppliers/report', [SupplierController::class, 'report'])->name('suppliers.report');
    Route::resource('suppliers', SupplierController::class);

    // Reports module.
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/daily-sales', [ReportController::class, 'dailySales'])->name('reports.daily-sales');
    Route::get('/reports/daily-sales/export', [ReportController::class, 'exportDailySales'])->name('reports.daily-sales.export');
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock'])->name('reports.low-stock');
    Route::get('/reports/low-stock/export', [ReportController::class, 'exportLowStock'])->name('reports.low-stock.export');
    Route::get('/reports/profit-margin', [ReportController::class, 'profitMargin'])->name('reports.profit-margin');
    Route::get('/reports/profit-margin/export', [ReportController::class, 'exportProfitMargin'])->name('reports.profit-margin.export');
    Route::get('/reports/cashier', [ReportController::class, 'cashier'])->name('reports.cashier');
    Route::get('/reports/cashier/export', [ReportController::class, 'exportCashier'])->name('reports.cashier.export');

    // Settings (admin only).
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/users/{user}/role', [SettingsController::class, 'updateUserRole'])->name('settings.users.role');
    Route::post('/settings/users/{user}/toggle', [SettingsController::class, 'toggleUser'])->name('settings.users.toggle');
    Route::get('/settings/backup', [SettingsController::class, 'backupDatabase'])->name('settings.backup');

    // Help / user manual.
    Route::get('/help', fn () => view('help'))->name('help');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
