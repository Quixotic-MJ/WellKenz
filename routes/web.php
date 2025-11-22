<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
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
    Route::get('/dashboard', [AdminController::class, 'systemOverview'])->name('dashboard');

    // User Management
    Route::get('/users', [AdminController::class, 'allUsers'])->name('users.index');
    Route::get('/roles', [AdminController::class, 'userRoles'])->name('roles.index');

    // User Management AJAX routes - KEEP route model binding
    Route::post('/users', [AdminController::class, 'createUser'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.destroy');
    Route::get('/users/search', [AdminController::class, 'searchUsers'])->name('users.search');
    
    // User password management - KEEP route model binding
    Route::post('/users/{user}/reset-password', [AdminController::class, 'resetUserPassword'])->name('users.reset-password');
    Route::post('/users/{user}/change-password', [AdminController::class, 'changeUserPassword'])->name('users.change-password');
    Route::post('/users/bulk-operations', [AdminController::class, 'bulkUserOperations'])->name('users.bulk-operations');

    // Role Management routes
    Route::get('/roles/{role}/details', [AdminController::class, 'getRoleDetails'])->name('roles.details');
    Route::post('/roles/{role}/permissions', [AdminController::class, 'saveRolePermissions'])->name('roles.permissions');
    Route::get('/roles/{role}/permissions', [AdminController::class, 'getRolePermissions'])->name('roles.permissions.get');
    Route::post('/roles/create', [AdminController::class, 'createRole'])->name('roles.create');

    // Master Files
    Route::get('/items', [AdminController::class, 'items'])->name('items.index');
    Route::post('/items', [AdminController::class, 'createItem'])->name('items.store');
    Route::get('/items/{item}/edit', [AdminController::class, 'editItem'])->name('items.edit');
    Route::put('/items/{item}', [AdminController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{item}', [AdminController::class, 'deleteItem'])->name('items.destroy');
    Route::get('/items/data', [AdminController::class, 'getItemData'])->name('items.data');

    // Categories Management
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [AdminController::class, 'createCategory'])->name('categories.store');
    Route::get('/categories/{category}/edit', [AdminController::class, 'editCategory'])->name('categories.edit');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::patch('/categories/{category}/toggle-status', [AdminController::class, 'toggleCategoryStatus'])->name('categories.toggle-status');
    Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.destroy');
    Route::get('/categories/parent', [AdminController::class, 'getParentCategories'])->name('categories.parent');
    Route::get('/categories/search', [AdminController::class, 'searchCategories'])->name('categories.search');

    Route::get('/units', [AdminController::class, 'units'])->name('units.index');
    Route::post('/units', [AdminController::class, 'createUnit'])->name('units.store');
    Route::get('/units/{unit}/edit', [AdminController::class, 'editUnit'])->name('units.edit');
    Route::put('/units/{unit}', [AdminController::class, 'updateUnit'])->name('units.update');
    Route::patch('/units/{unit}/toggle-status', [AdminController::class, 'toggleUnitStatus'])->name('units.toggle-status');
    Route::delete('/units/{unit}', [AdminController::class, 'deleteUnit'])->name('units.destroy');
    Route::get('/units/base', [AdminController::class, 'getBaseUnits'])->name('units.base');
    Route::get('/units/search', [AdminController::class, 'searchUnits'])->name('units.search');

    // External Partners
    Route::get('/suppliers', [AdminController::class, 'supplierList'])->name('suppliers.index');
    Route::post('/suppliers', [AdminController::class, 'storeSupplier'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}/edit', [AdminController::class, 'editSupplier'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [AdminController::class, 'updateSupplier'])->name('suppliers.update');
    Route::patch('/suppliers/{supplier}/toggle-status', [AdminController::class, 'toggleSupplierStatus'])->name('suppliers.toggle-status');
    Route::delete('/suppliers/{supplier}', [AdminController::class, 'deleteSupplier'])->name('suppliers.destroy');

    // System & Security
    Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    Route::post('/audit-logs/export', [AdminController::class, 'exportAuditLogs'])->name('audit-logs.export');
    Route::get('/audit-logs/{auditLog}/export', [AdminController::class, 'exportAuditLogProof'])->name('audit-logs.proof-export');
    Route::get('/audit-logs/{auditLog}', [AdminController::class, 'showAuditLog'])->name('audit-logs.show');

    Route::get('/settings', [AdminController::class, 'generalSettings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    Route::get('/backups', [AdminController::class, 'backup'])->name('backups');
    Route::post('/backups/create', [AdminController::class, 'createBackup'])->name('backups.create');
    Route::get('/backups/download/{filename}', [AdminController::class, 'downloadBackup'])->name('backups.download');
    Route::get('/backups/history', [AdminController::class, 'getBackupHistory'])->name('backups.history');
    Route::post('/backups/restore', [AdminController::class, 'restoreBackup'])->name('backups.restore');

    // Notifications
    Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications');
    
    // Notification management routes - More specific routes first to avoid model binding conflicts
    Route::get('/notifications/header', [AdminController::class, 'getHeaderNotifications'])->name('notifications.header');
    Route::get('/notifications/unread-count', [AdminController::class, 'getUnreadNotificationCount'])->name('notifications.unread_count');
    Route::post('/notifications/mark-all-read', [AdminController::class, 'markAllNotificationsAsRead'])->name('notifications.mark_all_read');
    Route::post('/notifications', [AdminController::class, 'createNotification'])->name('notifications.store');
    Route::post('/notifications/bulk-operations', [AdminController::class, 'bulkNotificationOperations'])->name('notifications.bulk_operations');
    
    // Routes with model binding - with constraints to prevent conflicts
    Route::get('/notifications/{notification}', [AdminController::class, 'getNotificationDetails'])->name('notifications.show')
        ->where('notification', '[0-9]+');
    Route::post('/notifications/{notification}/mark-read', [AdminController::class, 'markNotificationAsRead'])->name('notifications.mark_read')
        ->where('notification', '[0-9]+');
    Route::post('/notifications/{notification}/mark-unread', [AdminController::class, 'markNotificationAsUnread'])->name('notifications.mark_unread')
        ->where('notification', '[0-9]+');
    Route::delete('/notifications/{notification}', [AdminController::class, 'deleteNotification'])->name('notifications.destroy')
        ->where('notification', '[0-9]+');

});


// 2. SUPERVISOR ROUTES
// Security: Only users with role 'supervisor' can access
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [SupervisorController::class, 'home'])->name('dashboard');

    // Approvals
    Route::get('/approvals/requisitions', [SupervisorController::class, 'requisitionApprovals'])->name('approvals.requisitions');
    Route::get('/approvals/purchase-requests', [SupervisorController::class, 'purchaseRequestApprovals'])->name('approvals.purchase-requests');

    // Requisition Actions
    Route::patch('/requisitions/{requisition}/approve', [SupervisorController::class, 'approveRequisition'])->name('requisitions.approve');
    Route::patch('/requisitions/{requisition}/reject', [SupervisorController::class, 'rejectRequisition'])->name('requisitions.reject');
    Route::patch('/requisitions/{requisition}/modify', [SupervisorController::class, 'modifyRequisitionQuantity'])->name('requisitions.modify');
    Route::get('/requisitions/{requisition}/details', [SupervisorController::class, 'getRequisitionDetails'])->name('requisitions.details');
    
    // Purchase Request Actions
    Route::patch('/purchase-requests/{purchaseRequest}/approve', [SupervisorController::class, 'approvePurchaseRequest'])->name('purchase-requests.approve');
    Route::patch('/purchase-requests/{purchaseRequest}/reject', [SupervisorController::class, 'rejectPurchaseRequest'])->name('purchase-requests.reject');
    Route::get('/purchase-requests/{purchaseRequest}/details', [SupervisorController::class, 'getPurchaseRequestDetails'])->name('purchase-requests.details');
    
    // Bulk Operations
    Route::patch('/requisitions/bulk-approve', [SupervisorController::class, 'bulkApproveRequisitions'])->name('requisitions.bulk-approve');
    Route::patch('/purchase-requests/bulk-approve', [SupervisorController::class, 'bulkApprovePurchaseRequests'])->name('purchase-requests.bulk-approve');
    
    // Statistics and Analytics
    Route::get('/requisitions/statistics', [SupervisorController::class, 'getRequisitionStatistics'])->name('requisitions.statistics');

    // Notifications
    Route::get('/notifications', [SupervisorController::class, 'notifications'])->name('notifications');

    // Inventory Oversight
    Route::get('/inventory', [SupervisorController::class, 'stockLevel'])->name('inventory.index');
    Route::get('/inventory/history', [SupervisorController::class, 'stockHistory'])->name('inventory.history');
    Route::get('/inventory/history/{item}', [SupervisorController::class, 'stockCard'])->name('inventory.history.item');
    Route::get('/inventory/adjustments', [SupervisorController::class, 'inventoryAdjustments'])->name('inventory.adjustments');

    // Reports
    Route::get('/reports/yield', [SupervisorController::class, 'yieldVariance'])->name('reports.yield');
    Route::get('/reports/expiry', [SupervisorController::class, 'expiryReport'])->name('reports.expiry');
    Route::get('/reports/cogs', [SupervisorController::class, 'cogsReport'])->name('reports.cogs');

    // Settings
    Route::get('/settings/stock-levels', [SupervisorController::class, 'branchSetting'])->name('settings.stock-levels');

    // AJAX endpoints for dashboard
    Route::get('/stock-overview', [SupervisorController::class, 'getStockOverview'])->name('stock.overview');
    Route::get('/production-metrics', [SupervisorController::class, 'getProductionMetrics'])->name('production.metrics');

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

    // Notifications
    Route::get('/notifications', function () { 
        return view('Purchasing.notification'); 
    })->name('notifications');

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

    Route::get('/outbound/purchase-requests/create', [InventoryController::class, 'create'])->name('purchase-requests.create');

    Route::post('/outbound/purchase-requests', [InventoryController::class, 'createPurchaseRequest'])->name('purchase-requests.store');

    Route::get('/purchase-requests/{id}', [InventoryController::class, 'show'])->name('purchase-requests.show');

    Route::delete('/purchase-requests/{id}', [InventoryController::class, 'destroy'])->name('purchase-requests.destroy');

    Route::get('/purchase-requests/items', [InventoryController::class, 'getItems'])->name('purchase-requests.items');

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

    // Notifications
    Route::get('/notifications', function () { 
        return view('Inventory.notification'); 
    })->name('notifications');

});


// 5. STAFF / EMPLOYEE ROUTES (Baker)
// Security: Only users with role 'employee' can access
// Note: AuthController redirects to 'employee.dashboard', so we name it 'employee.'
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [EmployeeController::class, 'home'])->name('dashboard');

    // Requisitions
    Route::get('/requisitions/create', [EmployeeController::class, 'showCreateRequisition'])->name('requisitions.create');
    Route::post('/requisitions', [EmployeeController::class, 'createRequisition'])->name('requisitions.store');
    Route::get('/requisitions/history', [EmployeeController::class, 'requisitionHistory'])->name('requisitions.history');
    Route::get('/requisitions/{requisition}/details', [EmployeeController::class, 'getRequisitionDetails'])->name('requisitions.details');
    
    // Requisition Actions
    Route::post('/requisitions/{requisition}/confirm-receipt', [EmployeeController::class, 'confirmReceipt'])->name('requisitions.confirm-receipt');

    // Production
    Route::get('/production/log', [EmployeeController::class, 'productionLog'])->name('production.log');
    Route::post('/production/log', [EmployeeController::class, 'storeProduction'])->name('production.store');
    Route::get('/production/check-reject-support', [EmployeeController::class, 'checkRejectQuantitySupport'])->name('production.check-reject-support');
    Route::get('/recipes', [EmployeeController::class, 'recipes'])->name('recipes.index');
    Route::get('/recipes/{recipe}/details', [EmployeeController::class, 'getRecipeDetails'])->name('recipes.details');
    Route::post('/recipes', [EmployeeController::class, 'createRecipe'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [EmployeeController::class, 'updateRecipe'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [EmployeeController::class, 'deleteRecipe'])->name('recipes.destroy');

    // Notifications
    Route::get('/notifications', [EmployeeController::class, 'notifications'])->name('notifications');
    Route::get('/notifications/header', [EmployeeController::class, 'getHeaderNotifications'])->name('notifications.header');
    Route::post('/notifications/{notification}/mark-read', [EmployeeController::class, 'markNotificationAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/{notification}/mark-unread', [EmployeeController::class, 'markNotificationAsUnread'])->name('notifications.mark-unread');
    Route::post('/notifications/mark-all-read', [EmployeeController::class, 'markAllNotificationsAsRead'])->name('notifications.mark-all-read');

    // AJAX endpoints
    Route::get('/items/search', [EmployeeController::class, 'getItemsForRequisition'])->name('items.search');

});