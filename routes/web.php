<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// --- Import your controllers (Ready for when you move logic to controllers) ---
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
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
   PROTECTED ROUTES (Grouped by Role)
 ---------------------------------------------------------- */

// 1. ADMIN ROUTES
// Security: Only users with role 'admin' can access
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('Admin.system_overview');
    })->name('dashboard');

    // User Management
    Route::get('/users', function () {
        return view('Admin.user_management.all_user');
    })->name('users.index');

    Route::get('/roles', function () {
        return view('Admin.user_management.roles');
    })->name('roles.index');

    // Master Files
    Route::get('/items', function () {
        return view('Admin.master_files.item_masterlist');
    })->name('items.index');

    Route::get('/categories', function () {
        return view('Admin.master_files.categories');
    })->name('categories.index');

    Route::get('/units', function () {
        return view('Admin.master_files.unit_config');
    })->name('units.index');

    // External Partners
    Route::get('/suppliers', function () {
        return view('Admin.supplier.supplier_list');
    })->name('suppliers.index');

    // System & Security
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


// 2. SUPERVISOR ROUTES
// Security: Only users with role 'supervisor' can access
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('Supervisor.Home');
    })->name('dashboard');

    // Approvals
    Route::get('/approvals/requisitions', function () {
        return view('Supervisor.approvals.requisition');
    })->name('approvals.requisitions');

    Route::get('/approvals/purchase-requests', function () {
        return view('Supervisor.approvals.purchase_request');
    })->name('approvals.purchase-requests');

    // Inventory Oversight
    Route::get('/inventory', function () {
        return view('Supervisor.inventory.stock_level');
    })->name('inventory.index');

    Route::get('/inventory/history', function () {
        return view('Supervisor.inventory.stock_card');
    })->name('inventory.history');

    Route::get('/inventory/adjustments', function () {
        return view('Supervisor.inventory.adjustments');
    })->name('inventory.adjustments');

    // Reports
    Route::get('/reports/yield', function () {
        return view('Supervisor.reports.yield_variance');
    })->name('reports.yield');

    Route::get('/reports/expiry', function () {
        return view('Supervisor.reports.expiry_report');
    })->name('reports.expiry');

    Route::get('/reports/cogs', function () {
        return view('Supervisor.reports.COGS');
    })->name('reports.cogs');

    // Settings
    Route::get('/settings/stock-levels', function () {
        return view('Supervisor.branch_setting');
    })->name('settings.stock-levels');

});


// 3. PURCHASING ROUTES
// Security: Only users with role 'purchasing' can access
Route::middleware(['auth', 'role:purchasing'])->prefix('purchasing')->name('purchasing.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () { 
        return view('Purchasing.home'); 
    })->name('dashboard');

    // Purchase Orders
    Route::get('/po/create', function () { 
        return view('Purchasing.purchase_orders.create_po'); 
    })->name('po.create');

    Route::get('/po/drafts', function () { 
        return view('Purchasing.purchase_orders.drafts'); 
    })->name('po.drafts');

    Route::get('/po/open', function () { 
        return view('Purchasing.purchase_orders.open_orders'); 
    })->name('po.open');

    Route::get('/po/partial', function () { 
        return view('Purchasing.purchase_orders.partial_orders'); 
    })->name('po.partial');

    Route::get('/po/history', function () { 
        return view('Purchasing.purchase_orders.completed_history'); 
    })->name('po.history');

    // Suppliers
    Route::get('/suppliers', function () { 
        return view('Purchasing.suppliers.supplier_masterlist'); 
    })->name('suppliers.index');
    
    Route::get('/suppliers/prices', function () { 
        return view('Purchasing.suppliers.pricelist'); 
    })->name('suppliers.prices');

    // Reports & Delivery
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


// 4. INVENTORY ROUTES
// Security: Only users with role 'inventory' can access
Route::middleware(['auth', 'role:inventory'])->prefix('inventory')->name('inventory.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () { 
        return view('Inventory.home'); 
    })->name('dashboard');

    // Inbound
    Route::get('/inbound/receive', function () { 
        return view('Inventory.inbound.receive_delivery'); 
    })->name('inbound.receive');

    Route::get('/inbound/labels', function () { 
        return view('Inventory.inbound.print_batch_labels'); 
    })->name('inbound.labels');

    Route::get('/inbound/rtv', function () { 
        return view('Inventory.inbound.RTV'); 
    })->name('inbound.rtv');

    // Outbound
    Route::get('/outbound/fulfill', function () { 
        return view('Inventory.outbound.fullfill_request'); 
    })->name('outbound.fulfill');

    Route::get('/outbound/direct', function () { 
        return view('Inventory.outbound.direct_issuance'); 
    })->name('outbound.direct');

    // Stock Mgmt
    Route::get('/stock/count', function () { 
        return view('Inventory.stock_management.physical_count'); 
    })->name('stock.count');

    Route::get('/stock/lookup', function () { 
        return view('Inventory.stock_management.batch_lookup'); 
    })->name('stock.lookup');

    Route::get('/stock/transfer', function () { 
        return view('Inventory.stock_management.stock_transfer'); 
    })->name('stock.transfer');

});


// 5. STAFF / EMPLOYEE ROUTES (Baker)
// Security: Only users with role 'employee' can access
// Note: AuthController redirects to 'employee.dashboard', so we name it 'employee.'
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () { 
        return view('Employee.home'); 
    })->name('dashboard');

    // Requisitions
    Route::get('/requisitions/create', function () { 
        return view('Employee.requisition.create'); 
    })->name('requisitions.create');

    Route::get('/requisitions/history', function () { 
        return view('Employee.requisition.history'); 
    })->name('requisitions.history');

    // Production
    Route::get('/production/log', function () { 
        return view('Employee.production.log'); 
    })->name('production.log');

    Route::get('/recipes', function () { 
        return view('Employee.production.recipe'); 
    })->name('recipes.index');

});