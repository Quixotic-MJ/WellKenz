<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// --- Import your controllers ---
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SupervisorController;


/* ----------------------------------------------------------
    PUBLIC ROUTES (Guests can access)
 ---------------------------------------------------------- */

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


/* ----------------------------------------------------------
    PROTECTED DASHBOARD ROUTES
 ---------------------------------------------------------- */

Route::middleware(['auth'])->group(function () {

    /* ----------------------------------------------------------
        ADMIN DASHBOARD ROUTES (Direct View Returns)
     ---------------------------------------------------------- */

    // Prefix: /admin/url...
    // Name: admin.route_name
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // 1. Dashboard
        Route::get('/dashboard', function () {
            return view('Admin.system_overview');
        })->name('dashboard');

        // 2. User Management
        Route::get('/users', function () {
            return view('Admin.user_management.all_user');
        })->name('users.index');

        Route::get('/roles', function () {
            return view('Admin.user_management.roles');
        })->name('roles.index');

        // 3. Master Files
        Route::get('/items', function () {
            return view('Admin.master_files.item_masterlist');
        })->name('items.index');

        Route::get('/categories', function () {
            return view('Admin.master_files.categories');
        })->name('categories.index');

        Route::get('/units', function () {
            return view('Admin.master_files.unit_config');
        })->name('units.index');

        // 4. External Partners
        Route::get('/suppliers', function () {
            return view('Admin.supplier.supplier_list');
        })->name('suppliers.index');

        // 5. System & Security
        Route::get('/audit-logs', function () {
            return view('Admin.system.audit_logs');
        })->name('audit-logs');

        Route::get('/settings', function () {
            return view('Admin.system.general_setting');
        })->name('settings');

        Route::get('/backups', function () {
            return view('Admin.system.backup');
        })->name('backups');

    });


    /* ----------------------------------------------------------
        SUPERVISOR DASHBOARD ROUTES
     ---------------------------------------------------------- */
    
    // Prefix: /supervisor/url...
    // Name: supervisor.route_name
    Route::prefix('supervisor')->name('supervisor.')->group(function () {

        // 1. Dashboard
        Route::get('/dashboard', function () {
            return view('Supervisor.Home');
        })->name('dashboard');

        // 2. Approvals
        Route::get('/approvals/requisitions', function () {
            return view('Supervisor.approvals.requisition'); // Placeholder for now
        })->name('approvals.requisitions');

        Route::get('/approvals/purchase-requests', function () {
            return view('Supervisor.approvals.purchase_request'); // Placeholder
        })->name('approvals.purchase-requests');

        // 3. Inventory Oversight
        Route::get('/inventory', function () {
            return view('Supervisor.inventory.stock_level'); // Placeholder
        })->name('inventory.index');

        Route::get('/inventory/history', function () {
            return view('Supervisor.inventory.stock_card'); // Placeholder
        })->name('inventory.history');

        Route::get('/inventory/adjustments', function () {
            return view('Supervisor.inventory.adjustments'); // Placeholder
        })->name('inventory.adjustments');

        // 4. Reports
        Route::get('/reports/yield', function () {
            return view('Supervisor.reports.yield_variance'); // Placeholder
        })->name('reports.yield');

        Route::get('/reports/expiry', function () {
            return view('Supervisor.reports.expiry_report'); // Placeholder
        })->name('reports.expiry');

        Route::get('/reports/cogs', function () {
            return view('Supervisor.reports.COGS'); // Placeholder
        })->name('reports.cogs');

        // 5. Settings
        Route::get('/settings/stock-levels', function () {
            return view('Supervisor.branch_setting'); // Placeholder
        })->name('settings.stock-levels');

    });

    /* ----------------------------------------------------------
        PURCHASING DASHBOARD ROUTES (New)
     ---------------------------------------------------------- */
    // Prefix: /purchasing/url...
    // Name: purchasing.route_name
    Route::prefix('purchasing')->name('purchasing.')->group(function () {
        
        // 1. Dashboard
        Route::get('/dashboard', function () { 
            // We will create this view next
            return view('Purchasing.home'); 
        })->name('dashboard');

        // 2. Purchase Orders
        Route::get('/po/create', function () { 
            return view('Purchasing.purchase_orders.create_po'); // Placeholder
        })->name('po.create');

        Route::get('/po/drafts', function () { 
            return view('Purchasing.purchase_orders.drafts'); // Placeholder
        })->name('po.drafts');

        Route::get('/po/open', function () { 
            return view('Purchasing.purchase_orders.open_orders'); // Placeholder
        })->name('po.open');

        Route::get('/po/partial', function () { 
            return view('Purchasing.purchase_orders.partial_orders'); // Placeholder
        })->name('po.partial');

        Route::get('/po/history', function () { 
            return view('Purchasing.purchase_orders.completed_history'); // Placeholder
        })->name('po.history');

        // 3. Suppliers
        Route::get('/suppliers', function () { 
            return view('Purchasing.suppliers.supplier_masterlist'); // Placeholder
        })->name('suppliers.index');
        
        Route::get('/suppliers/prices', function () { 
            return view('Purchasing.suppliers.pricelist'); // Placeholder
        })->name('suppliers.prices');

        // 4. Reports & Delivery
        Route::get('/reports/history', function () { 
            return view('Purchasing.reports.purchase_history'); 
        })->name('reports.history');
        
        Route::get('/reports/performance', function () { 
            return view('Purchasing.reports.supplier_performance'); 
        })->name('reports.performance');
        
        Route::get('/reports/rtv', function () { 
            return view('Purchasing.reports.RTV'); 
        })->name('reports.rtv');

    });

    /* ----------------------------------------------------------
        INVENTORY DASHBOARD ROUTES
     ---------------------------------------------------------- */
    // Prefix: /inventory/url...
    // Name: inventory.route_name
    Route::prefix('inventory')->name('inventory.')->group(function () {
        
        // 1. Dashboard
        Route::get('/dashboard', function () { 
            return view('Inventory.dashboard'); 
        })->name('dashboard');

        // 2. Inbound
        Route::get('/inbound/receive', function () { 
            return view('Inventory.inbound.receive'); // Placeholder
        })->name('inbound.receive');

        Route::get('/inbound/labels', function () { 
            return view('Inventory.dashboard'); // Placeholder
        })->name('inbound.labels');

        Route::get('/inbound/rtv', function () { 
            return view('Inventory.dashboard'); // Placeholder
        })->name('inbound.rtv');

        // 3. Outbound
        Route::get('/outbound/fulfill', function () { 
            return view('Inventory.dashboard'); // Placeholder
        })->name('outbound.fulfill');

        Route::get('/outbound/direct', function () { 
            return view('Inventory.dashboard'); // Placeholder
        })->name('outbound.direct');

        // 4. Stock Mgmt
        Route::get('/stock/count', function () { 
            return view('Inventory.dashboard'); // Placeholder
        })->name('stock.count');

        Route::get('/stock/lookup', function () { 
            return view('Inventory.dashboard'); // Placeholder
        })->name('stock.lookup');

        Route::get('/stock/transfer', function () { 
            return view('Inventory.dashboard'); // Placeholder
        })->name('stock.transfer');

   
    });

});