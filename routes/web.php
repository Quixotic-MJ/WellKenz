<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// --- Import your controllers (Ready for when you move logic to controllers) ---

// Admin namespace controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagement\UserController;
use App\Http\Controllers\Admin\UserManagement\RoleController;
use App\Http\Controllers\Admin\MasterData\ItemController;
use App\Http\Controllers\Admin\MasterData\CategoryController;
use App\Http\Controllers\Admin\MasterData\UnitController;
use App\Http\Controllers\Admin\System\AuditLogController;
use App\Http\Controllers\Admin\System\NotificationController;
use App\Http\Controllers\Admin\Partner\SupplierController;

// Inventory namespace controllers
use App\Http\Controllers\Inventory\GeneralController;
use App\Http\Controllers\Inventory\Inbound\ReceivingController;
use App\Http\Controllers\Inventory\Inbound\BatchController; 
use App\Http\Controllers\Inventory\Inbound\RtvController;
use App\Http\Controllers\Inventory\Outbound\FulfillmentController;
use App\Http\Controllers\Inventory\Outbound\PurchaseRequestController;
use App\Http\Controllers\Inventory\StockManagement\BatchLookupController;
use App\Http\Controllers\Inventory\StockManagement\StockLevelController;
use App\Http\Controllers\Inventory\Notifications\NotificationController as InventoryNotificationController;

// Purchasing namespace controllers (aliased to avoid conflicts)
use App\Http\Controllers\Purchasing\DashboardController as PurchasingDashboardController;
use App\Http\Controllers\Purchasing\PurchaseOrderController as PurchasingPOController;
use App\Http\Controllers\Purchasing\SupplierController as PurchasingSupplierController;
use App\Http\Controllers\Purchasing\PriceListController as PurchasingPriceListController;
use App\Http\Controllers\Purchasing\ReportController as PurchasingReportController;
use App\Http\Controllers\Purchasing\Notifications\NotificationController as PurchasingNotificationController;

// Employee namespace controllers
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\RequisitionController as EmployeeRequisitionController;
use App\Http\Controllers\Employee\ProductionController as EmployeeProductionController;
use App\Http\Controllers\Employee\RecipeController as EmployeeRecipeController;
use App\Http\Controllers\Employee\Notifications\NotificationController as EmployeeNotificationController;

// Supervisor namespace controllers (incremental split)
use App\Http\Controllers\Supervisor\InventoryController as SupervisorInventoryController;
use App\Http\Controllers\Supervisor\ReportsController as SupervisorReportsController;
use App\Http\Controllers\Supervisor\SettingsController as SupervisorSettingsController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\ApprovalsController as SupervisorApprovalsController;
use App\Http\Controllers\Supervisor\Notifications\NotificationController as SupervisorNotificationController;
use App\Http\Controllers\Supervisor\RequisitionController as SupervisorRequisitionController;
use App\Http\Controllers\ProfileController;

/* ----------------------------------------------------------
   PUBLIC ROUTES (Guests can access)
 ---------------------------------------------------------- */

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ----------------------------------------------------------
   SHARED PROFILE ROUTES (All authenticated users)
 ---------------------------------------------------------- */

// Profile routes accessible to all authenticated users regardless of role
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
    Route::patch('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
});

/* ----------------------------------------------------------
   PROTECTED ROUTES (Grouped by Role)
 ---------------------------------------------------------- */

// 1. ADMIN ROUTES
// Security: Only users with role 'admin' can access
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'systemOverview'])->name('dashboard');

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/change-password', [UserController::class, 'changePassword'])->name('change-password');
        Route::post('/bulk-operations', [UserController::class, 'bulkOperations'])->name('bulk-operations');
        Route::get('/data', [UserController::class, 'getUserData'])->name('data');
        Route::get('/search', [UserController::class, 'search'])->name('search');
        Route::post('/filter', [UserController::class, 'filterUsers'])->name('filter');
        Route::get('/export', [UserController::class, 'exportUsers'])->name('export');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{role}/details', [RoleController::class, 'details'])->name('details');
        Route::post('/{role}/permissions', [RoleController::class, 'savePermissions'])->name('permissions');
        Route::get('/{role}/permissions', [RoleController::class, 'getPermissions'])->name('permissions.get');
    });

    // Master Data - Items
    Route::prefix('items')->name('items.')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('index');
        Route::post('/', [ItemController::class, 'store'])->name('store');
        Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('edit')
            ->where('item', '[0-9]+'); // Explicit constraint for numeric IDs
        Route::put('/{item}', [ItemController::class, 'update'])->name('update')
            ->where('item', '[0-9]+'); // Explicit constraint for numeric IDs
        Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy')
            ->where('item', '[0-9]+'); // Explicit constraint for numeric IDs
        Route::patch('/{item}/deactivate', [ItemController::class, 'deactivate'])->name('deactivate')
            ->where('item', '[0-9]+'); // Alternative soft delete route
        Route::patch('/{item}/reactivate', [ItemController::class, 'reactivate'])->name('reactivate')
            ->where('item', '[0-9]+'); // Reactivate deactivated item
        Route::get('/data', [ItemController::class, 'getItemData'])->name('data');
        Route::get('/search', [ItemController::class, 'search'])->name('search');
        Route::post('/generate-code', [ItemController::class, 'generateItemCode'])->name('generate-code');
        Route::get('/export', [ItemController::class, 'export'])->name('export');
        Route::post('/import', [ItemController::class, 'import'])->name('import');
    });

    // Master Data - Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::get('/parent', [CategoryController::class, 'getParentCategories'])->name('parent');
        Route::get('/search', [CategoryController::class, 'search'])->name('search');
    });

    // Master Data - Units
    Route::prefix('units')->name('units.')->group(function () {
        Route::get('/', [UnitController::class, 'index'])->name('index');
        Route::post('/', [UnitController::class, 'store'])->name('store');
        Route::get('/{unit}/edit', [UnitController::class, 'edit'])->name('edit');
        Route::put('/{unit}', [UnitController::class, 'update'])->name('update');
        Route::patch('/{unit}/toggle-status', [UnitController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{unit}', [UnitController::class, 'destroy'])->name('destroy');
        Route::get('/base', [UnitController::class, 'getBaseUnits'])->name('base');
        Route::get('/search', [UnitController::class, 'search'])->name('search');
    });

    // Partners - Supplier Management
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::get('/{supplier}/edit', [SupplierController::class, 'edit'])->name('edit')
            ->where('supplier', '[0-9]+'); // Explicit constraint for numeric IDs
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update')
            ->where('supplier', '[0-9]+'); // Explicit constraint for numeric IDs
        Route::patch('/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('toggle-status')
            ->where('supplier', '[0-9]+'); // Explicit constraint for numeric IDs
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy')
            ->where('supplier', '[0-9]+'); // Explicit constraint for numeric IDs
        Route::get('/search', [SupplierController::class, 'search'])->name('search');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('show')
            ->where('supplier', '[0-9]+'); // Explicit constraint for numeric IDs
        // Export routes
        Route::get('/export/csv', [SupplierController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf', [SupplierController::class, 'exportPdf'])->name('export.pdf');
    });

    // System & Security - Audit Logs
    Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
        Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
        Route::post('/export', [AuditLogController::class, 'export'])->name('export');
        Route::get('/{auditLog}/export', [AuditLogController::class, 'exportProof'])->name('proof-export');
        Route::get('/tables', [AuditLogController::class, 'getTableNames'])->name('tables');
        Route::get('/actions', [AuditLogController::class, 'getActions'])->name('actions');
    });

    // Notifications - Admin System Management
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/header', [NotificationController::class, 'getHeaderNotifications'])->name('header');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadNotificationCount'])->name('unread_count');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark_all_read');
        Route::post('/', [NotificationController::class, 'store'])->name('store');
        Route::post('/bulk-operations', [NotificationController::class, 'bulkOperations'])->name('bulk_operations');
        Route::get('/{notification}', [NotificationController::class, 'getNotificationDetails'])->name('show')
            ->where('notification', '[0-9]+');
        Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark_read')
            ->where('notification', '[0-9]+');
        Route::post('/{notification}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('mark_unread')
            ->where('notification', '[0-9]+');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy')
            ->where('notification', '[0-9]+');
    });

});


// 2. SUPERVISOR ROUTES
// Security: Only users with role 'supervisor' can access
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [SupervisorDashboardController::class, 'home'])->name('dashboard');

    // Legacy Approvals Routes (deprecated, use new requisition routes below)
    Route::get('/approvals/requisitions', [SupervisorApprovalsController::class, 'requisitionApprovals'])->name('approvals.requisitions');
    Route::get('/approvals/purchase-requests', [SupervisorApprovalsController::class, 'purchaseRequestApprovals'])->name('approvals.purchase-requests');

    // NEW Requisition Approval System
    Route::prefix('requisitions')->name('requisitions.')->group(function () {
        // Main requisitions dashboard
        Route::get('/', [SupervisorRequisitionController::class, 'index'])->name('index');
        
        // Requisition actions
        Route::get('/{requisition}/details', [SupervisorRequisitionController::class, 'getDetails'])->name('details');
        Route::patch('/{requisition}/approve', [SupervisorRequisitionController::class, 'approve'])->name('approve');
        Route::patch('/{requisition}/reject', [SupervisorRequisitionController::class, 'reject'])->name('reject');
        
        // Bulk operations
        Route::patch('/bulk-approve', [SupervisorRequisitionController::class, 'bulkApprove'])->name('bulk-approve');
        
        // AJAX endpoints for dynamic updates
        Route::get('/api/filtered', [SupervisorRequisitionController::class, 'getFiltered'])->name('api.filtered');
        Route::get('/api/refresh', [SupervisorRequisitionController::class, 'refresh'])->name('api.refresh');
        Route::get('/api/statistics', [SupervisorRequisitionController::class, 'getStatistics'])->name('api.statistics');
        
        // Export functionality
        Route::get('/export', [SupervisorRequisitionController::class, 'export'])->name('export');
    });

    // Legacy Requisition Actions (deprecated)
    Route::patch('/requisitions/{requisition}/modify', [SupervisorApprovalsController::class, 'modifyRequisitionQuantity'])->name('requisitions.modify');
    Route::patch('/requisitions/{requisition}/modify-multi', [SupervisorApprovalsController::class, 'modifyMultipleRequisitionItems'])->name('requisitions.modify-multi');
    
    // Purchase Request Actions
    Route::patch('/purchase-requests/{purchaseRequest}/approve', [SupervisorApprovalsController::class, 'approvePurchaseRequest'])->name('purchase-requests.approve');
    Route::patch('/purchase-requests/{purchaseRequest}/reject', [SupervisorApprovalsController::class, 'rejectPurchaseRequest'])->name('purchase-requests.reject');
    Route::get('/purchase-requests/{purchaseRequest}/details', [SupervisorApprovalsController::class, 'getPurchaseRequestDetails'])->name('purchase-requests.details');
    
    // Bulk Operations
    Route::patch('/requisitions/bulk-approve', [SupervisorApprovalsController::class, 'bulkApproveRequisitions'])->name('requisitions.bulk-approve');
    Route::patch('/purchase-requests/bulk-approve', [SupervisorApprovalsController::class, 'bulkApprovePurchaseRequests'])->name('purchase-requests.bulk-approve');
    
    // Statistics and Analytics
    Route::get('/requisitions/statistics', [SupervisorApprovalsController::class, 'getRequisitionStatistics'])->name('requisitions.statistics');

    // Notifications
    Route::get('/notifications', [SupervisorNotificationController::class, 'index'])->name('notifications');
    
    // Notification management routes - More specific routes first to avoid model binding conflicts
    Route::get('/notifications/header', [SupervisorNotificationController::class, 'getHeaderNotifications'])->name('notifications.header');
    Route::get('/notifications/unread-count', [SupervisorNotificationController::class, 'getUnreadNotificationCount'])->name('notifications.unread_count');
    Route::post('/notifications/mark-all-read', [SupervisorNotificationController::class, 'markAllNotificationsAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/bulk-operations', [SupervisorNotificationController::class, 'bulkNotificationOperations'])->name('notifications.bulk-operations');
    
    // Routes with model binding - with constraints to prevent conflicts
    Route::post('/notifications/{notification}/mark-read', [SupervisorNotificationController::class, 'markNotificationAsRead'])->name('notifications.mark-read')
        ->where('notification', '[0-9]+');
    Route::post('/notifications/{notification}/mark-unread', [SupervisorNotificationController::class, 'markNotificationAsUnread'])->name('notifications.mark-unread')
        ->where('notification', '[0-9]+');
    Route::delete('/notifications/{notification}', [SupervisorNotificationController::class, 'deleteNotification'])->name('notifications.destroy')
        ->where('notification', '[0-9]+');

    // Inventory Oversight
    Route::get('/inventory', [SupervisorInventoryController::class, 'stockLevel'])->name('inventory.stock-level');
    Route::get('/inventory/export-csv', [SupervisorInventoryController::class, 'exportStockCSV'])->name('inventory.export-stock-csv');
    Route::get('/inventory/export-pdf', [SupervisorInventoryController::class, 'exportStockPDF'])->name('inventory.export-stock-pdf');
    Route::get('/inventory/print-report', [SupervisorInventoryController::class, 'printStockReport'])->name('inventory.print-stock-report');
    Route::get('/inventory/history', [SupervisorInventoryController::class, 'stockHistory'])->name('inventory.stock-history');
    Route::get('/inventory/card/{item}', [SupervisorInventoryController::class, 'stockCard'])->name('inventory.stock-card');

    
    // Inventory Adjustments API endpoints
    Route::get('/inventory/adjustments/items/{item}', [SupervisorInventoryController::class, 'getItemDetails'])->name('inventory.adjustments.item-details');
    Route::post('/inventory/adjustments', [SupervisorInventoryController::class, 'createAdjustment'])->name('inventory.adjustments.store');
    Route::get('/inventory/adjustments/history', [SupervisorInventoryController::class, 'getAdjustmentHistory'])->name('inventory.adjustments.history');

    // Reports
    Route::get('/reports/expiry', [SupervisorReportsController::class, 'expiryReport'])->name('reports.expiry');
    Route::get('/reports/batch/{batchId}/details', [SupervisorReportsController::class, 'getBatchDetails'])->name('reports.batch.details');
    // Use First List and Alerts
    Route::get('/reports/print-use-first-list', [SupervisorReportsController::class, 'printUseFirstList'])->name('reports.print_use_first_list');
    Route::get('/reports/export-use-first-list-pdf', [SupervisorReportsController::class, 'exportUseFirstListPDF'])->name('reports.export_use_first_list_pdf');
    Route::post('/reports/alert-bakers', [SupervisorReportsController::class, 'alertBakers'])->name('reports.alert_bakers');

    // Settings
    Route::get('/settings/stock-levels', [SupervisorSettingsController::class, 'branchSetting'])->name('settings.stock-levels');
    // Stock Level Configuration AJAX endpoints
    Route::post('/settings/stock-levels/update', [SupervisorSettingsController::class, 'updateMinimumStockLevel'])->name('settings.stock-levels.update');
    Route::post('/settings/stock-levels/seasonal-adjustment', [SupervisorSettingsController::class, 'applySeasonalAdjustment'])->name('settings.stock-levels.seasonal-adjustment');
    Route::get('/settings/stock-levels/data', [SupervisorSettingsController::class, 'getStockConfigurationData'])->name('settings.stock-levels.data');

    // AJAX endpoints for dashboard
    Route::get('/stock-overview', [SupervisorDashboardController::class, 'getStockOverview'])->name('stock.overview');
    Route::get('/production-metrics', [SupervisorDashboardController::class, 'getProductionMetrics'])->name('production.metrics');

});


// 3. PURCHASING ROUTES
// Security: Only users with role 'purchasing' can access
Route::middleware(['auth', 'role:purchasing'])->prefix('purchasing')->name('purchasing.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [PurchasingDashboardController::class, 'home'])->name('dashboard');

    // Purchase Orders
    Route::get('/po/create', [PurchasingPOController::class, 'createPurchaseOrder'])->name('po.create');
    Route::post('/po', [PurchasingPOController::class, 'storePurchaseOrder'])->name('po.store');
    Route::get('/api/supplier/{supplier}/data', [PurchasingPOController::class, 'getSupplierData'])->name('api.supplier.data');

    Route::post('/po/bulk-create', [PurchasingPOController::class, 'bulkCreatePurchaseOrders'])->name('po.bulk-create');
    Route::post('/po/bulk-confirm', [PurchasingPOController::class, 'bulkConfirmOrders'])->name('po.bulk-confirm');
    
    // Specific routes first - these must come before the generic {purchaseOrder} route
    Route::get('/po/open', [PurchasingPOController::class, 'openOrders'])->name('po.open');
    Route::get('/po/partial', [PurchasingPOController::class, 'partialOrders'])->name('po.partial');
    Route::get('/po/history', [PurchasingReportController::class, 'completedHistory'])->name('po.history');

    
    // Generic routes with {purchaseOrder} parameter - these must come after specific routes
    Route::get('/po/{purchaseOrder}', [PurchasingPOController::class, 'showPurchaseOrder'])->name('po.show');
    Route::get('/po/{purchaseOrder}/print', [PurchasingPOController::class, 'printPurchaseOrder'])->name('po.print');
    Route::get('/po/{purchaseOrder}/pdf', [PurchasingPOController::class, 'downloadPDF'])->name('po.pdf');
    
    // PO Actions - these must come after the generic route
    Route::patch('/po/{purchaseOrder}/submit', [PurchasingPOController::class, 'submitPurchaseOrder'])->name('po.submit');
    Route::patch('/po/{purchaseOrder}/acknowledge', [PurchasingPOController::class, 'acknowledgePurchaseOrder'])->name('po.acknowledge');
    Route::get('/po/{purchaseOrder}/edit', [PurchasingPOController::class, 'editPurchaseOrder'])->name('po.edit');
    Route::delete('/po/{purchaseOrder}', [PurchasingPOController::class, 'destroyPurchaseOrder'])->name('po.destroy');

    // Suppliers
    Route::get('/suppliers', [PurchasingSupplierController::class, 'suppliers'])->name('suppliers.index');
    Route::post('/suppliers', [PurchasingSupplierController::class, 'storeSupplier'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [PurchasingSupplierController::class, 'updateSupplier'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [PurchasingSupplierController::class, 'destroySupplier'])->name('suppliers.destroy');
    Route::patch('/suppliers/{supplier}/toggle-status', [PurchasingSupplierController::class, 'toggleSupplierStatus'])->name('suppliers.toggle-status');
    
    // Supplier Items Management
    Route::get('/suppliers/{supplier}/items', [PurchasingSupplierController::class, 'getSupplierItems'])->name('suppliers.items');
    Route::post('/suppliers/{supplier}/items', [PurchasingSupplierController::class, 'addSupplierItems'])->name('suppliers.items.add');
    Route::get('/suppliers/{supplier}/available-items', [PurchasingSupplierController::class, 'getAvailableItems'])->name('suppliers.available-items');
    Route::get('/supplier-items/{supplierItem}', [PurchasingSupplierController::class, 'showSupplierItem'])->name('supplier-items.show');
    Route::patch('/supplier-items/{supplierItem}', [PurchasingSupplierController::class, 'updateSupplierItem'])->name('supplier-items.update');
    Route::delete('/supplier-items/{supplierItem}', [PurchasingSupplierController::class, 'removeSupplierItem'])->name('supplier-items.remove');
    Route::post('/suppliers/bulk-update-prices', [PurchasingSupplierController::class, 'bulkUpdateSupplierItemPrices'])->name('suppliers.bulk-update-prices');
    Route::get('/suppliers/prices', [PurchasingPriceListController::class, 'supplierPriceList'])->name('suppliers.prices');
    Route::get('/suppliers/prices/export', [PurchasingPriceListController::class, 'exportSupplierPriceList'])->name('suppliers.prices.export');
    Route::get('/suppliers/prices/update', [PurchasingPriceListController::class, 'showPriceUpdate'])->name('suppliers.prices.update');
    Route::get('/suppliers/prices/update/{supplierItem}', [PurchasingPriceListController::class, 'showPriceUpdate'])->name('suppliers.prices.update.single');
    Route::patch('/suppliers/prices/{supplierItem}', [PurchasingPriceListController::class, 'updateSupplierItemPrice'])->name('suppliers.prices.update.item');
    Route::post('/suppliers/prices/bulk-update', [PurchasingPriceListController::class, 'bulkUpdateSupplierPrices'])->name('suppliers.prices.bulk-update');
    Route::get('/suppliers/items-for-edit', [PurchasingPriceListController::class, 'getSupplierItemsForEdit'])->name('suppliers.items-for-edit');

    // Reports & Delivery
    Route::get('/reports/history', [PurchasingReportController::class, 'purchaseHistory'])->name('reports.history');
    Route::get('/reports/performance', [PurchasingReportController::class, 'supplierPerformance'])->name('reports.performance');
    Route::get('/reports/rtv', [PurchasingReportController::class, 'rtv'])->name('reports.rtv');

    // Notifications
    Route::get('/notifications', [PurchasingNotificationController::class, 'index'])->name('notifications');
    
    // Notification management routes
    Route::get('/notifications/header', [PurchasingNotificationController::class, 'getHeaderNotifications'])->name('notifications.header');
    Route::get('/notifications/stats', [PurchasingNotificationController::class, 'getNotificationStats'])->name('notifications.stats');
    Route::post('/notifications/mark-all-read', [PurchasingNotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::post('/notifications/bulk-operations', [PurchasingNotificationController::class, 'bulkOperations'])->name('notifications.bulk_operations');
    
    // Routes with model binding
    Route::post('/notifications/{notification}/mark-read', [PurchasingNotificationController::class, 'markNotificationAsRead'])->name('notifications.mark_read');
    Route::post('/notifications/{notification}/mark-unread', [PurchasingNotificationController::class, 'markNotificationAsUnread'])->name('notifications.mark_unread');
    Route::delete('/notifications/{notification}', [PurchasingNotificationController::class, 'destroy'])->name('notifications.destroy');

    // API Routes for AJAX functionality
    Route::get('/api/suppliers/search', [PurchasingSupplierController::class, 'searchSuppliers'])->name('api.suppliers.search');
    Route::get('/api/suppliers/{supplier}', [PurchasingSupplierController::class, 'getSupplierDetails'])->name('api.suppliers.details');
    Route::get('/api/items/search', [PurchasingPOController::class, 'searchItems'])->name('api.items.search');
    Route::get('/api/suppliers/{supplier}/items', [PurchasingPriceListController::class, 'getSupplierItems'])->name('api.suppliers.items');
    Route::post('/api/suppliers/{supplier}/items-for-prs', [PurchasingPOController::class, 'getSupplierItemsForPRs'])->name('api.suppliers.items-for-prs');
    Route::post('/api/items-for-prs', [PurchasingPOController::class, 'getItemsForPRs'])->name('api.items-for-prs');
    Route::post('/api/group-pr-items', [PurchasingPOController::class, 'groupPurchaseRequestItems'])->name('api.group-pr-items');
    Route::post('/api/get-pr-items', [PurchasingPOController::class, 'getPurchaseRequestItems'])->name('api.get-pr-items');
    Route::get('/api/dashboard/metrics', [PurchasingDashboardController::class, 'getDashboardMetrics'])->name('api.dashboard.metrics');
    Route::get('/api/dashboard/summary', [PurchasingDashboardController::class, 'getDashboardSummary'])->name('api.dashboard.summary');
    Route::get('/api/purchase-requests/{purchaseRequest}', [PurchasingPOController::class, 'getPurchaseRequestDetails'])->name('api.purchase-requests.details');

});


// 4. INVENTORY ROUTES
// Security: Only users with role 'inventory' can access
Route::middleware(['auth', 'role:inventory'])->prefix('inventory')->name('inventory.')->group(function () {
    
    // Dashboard and General Routes
    Route::get('/home', [GeneralController::class, 'home'])->name('dashboard');

    // Purchase Orders (for viewing/managing POs)
    Route::get('/purchase-orders', [PurchasingPOController::class, 'openOrders'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchasingPOController::class, 'createPurchaseOrder'])->name('purchase-orders.create');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchasingPOController::class, 'showPurchaseOrder'])->name('purchase-orders.show');
    
    // FEFO Batch Picking and Requisition Processing
    Route::post('/requisitions/{requisitionId}/start-picking', [FulfillmentController::class, 'startPicking'])->name('requisitions.start-picking');
    Route::post('/batches/{batchId}/pick', [FulfillmentController::class, 'pickBatch'])->name('batches.pick');
    

    // Inbound Routes - Receiving
    Route::prefix('inbound')->name('inbound.')->group(function () {
        // Delivery Receiving Routes
        Route::get('/receive', [ReceivingController::class, 'receiveDelivery'])->name('receive');
        Route::get('/purchase-orders/{id}/receive', [ReceivingController::class, 'getPurchaseOrder'])->name('purchase-orders.receive');
        Route::get('/purchase-orders-search', [ReceivingController::class, 'searchPurchaseOrder'])->name('purchase-orders.search');
        Route::post('/receive-delivery/process', [ReceivingController::class, 'processDelivery'])->name('receive-delivery.process');
        Route::post('/receive-delivery/validate', [ReceivingController::class, 'validateDeliveryData'])->name('receive-delivery.validate');
        Route::get('/receive-delivery/statistics', [ReceivingController::class, 'getReceivingStatistics'])->name('receive-delivery.statistics');

        // Batch Management Routes
        Route::get('/batch-logs', [BatchController::class, 'batchLogs'])->name('batch-logs');
        Route::get('/batch-logs/{id}/details', [BatchController::class, 'getBatchDetails'])->name('batch-logs.details');
        Route::get('/batch-logs/{id}/edit', [BatchController::class, 'editBatch'])->name('batch-logs.edit');
        Route::patch('/batch-logs/{id}/status', [BatchController::class, 'updateBatchStatus'])->name('batch-logs.status');
        Route::post('/batch-logs/export', [BatchController::class, 'exportBatchLogs'])->name('batch-logs.export');

        // Batch Labels Printing Routes
        Route::get('/labels', [BatchController::class, 'batchLogs'])->name('labels');
        Route::get('/labels/batch/{batchId}', [BatchController::class, 'getBatchForPrint'])->name('labels.batch');
        Route::post('/labels/print', [BatchController::class, 'printBatchLabelsProcess'])->name('labels.print');

        // RTV (Return to Vendor) Routes
        Route::get('/rtv', [RtvController::class, 'indexRtv'])->name('rtv');
        Route::get('/rtv/items', [RtvController::class, 'getItemsForRtv'])->name('rtv.items');
        Route::get('/rtv/suppliers', [RtvController::class, 'getSuppliersForRtv'])->name('rtv.suppliers');
        Route::get('/rtv/purchase-orders', [RtvController::class, 'getPurchaseOrdersForRtv'])->name('rtv.purchase-orders');
        Route::get('/rtv/po-items/{id}', [RtvController::class, 'getPoItemsForRtv'])->name('rtv.po-items');
        Route::get('/rtv/categories', [RtvController::class, 'getCategoriesForRtvBulk'])->name('rtv.categories');
        Route::post('/rtv', [RtvController::class, 'storeRtv'])->name('rtv.store');
        Route::get('/rtv/{id}/details', [RtvController::class, 'getRtvDetails'])->name('rtv.details');
        Route::delete('/rtv/{id}', [RtvController::class, 'deleteRtv'])->name('rtv.delete');
        Route::get('/rtv/{id}/print', [RtvController::class, 'printRtvSlip'])->name('rtv.print');
        Route::patch('/rtv/{id}/status', [RtvController::class, 'updateRtvStatus'])->name('rtv.status');
    });

    //// Outbound Routes
    Route::get('/outbound/fulfill', [FulfillmentController::class, 'fulfillRequests'])->name('outbound.fulfill');
    Route::get('/outbound/requisitions/{requisition}/details', [FulfillmentController::class, 'getRequisitionDetails'])->name('outbound.requisition.details');
    Route::post('/outbound/track-picking', [FulfillmentController::class, 'trackPicking'])->name('outbound.track-picking');
    Route::post('/outbound/confirm-issuance', [FulfillmentController::class, 'confirmIssuance'])->name('outbound.confirm-issuance');

    // Purchase Requests - Main interface (catalog + history)
    Route::get('/outbound/purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
    Route::get('/outbound/purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create');
    Route::post('/outbound/purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
    Route::get('/purchase-requests/{id}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
    Route::delete('/purchase-requests/{id}', [PurchaseRequestController::class, 'destroy'])->name('purchase-requests.destroy');

    // API endpoints for purchase requests
    Route::get('/purchase-requests/items', [PurchaseRequestController::class, 'getItems'])->name('purchase-requests.items');
    Route::get('/purchase-requests/categories', [PurchaseRequestController::class, 'getCategories'])->name('purchase-requests.categories');
    Route::get('/purchase-requests/departments', [PurchaseRequestController::class, 'getDepartments'])->name('purchase-requests.departments');




    Route::get('/stock/lookup', [BatchLookupController::class, 'batchLookup'])->name('stock.lookup');
  
  // AJAX routes for batch lookup
  Route::get('/stock/lookup/search', [BatchLookupController::class, 'searchBatches'])->name('stock.lookup.search');
  Route::get('/stock/lookup/batch/{id}', [BatchLookupController::class, 'getBatchDetails'])->name('stock.lookup.batch');

  // Live Stock Levels Routes
  Route::get('/stock/levels', [StockLevelController::class, 'index'])->name('stock.levels');
  Route::get('/stock/levels/data', [StockLevelController::class, 'getStockLevels'])->name('stock.levels.data');
  Route::post('/stock/levels/update', [StockLevelController::class, 'updateStockLevel'])->name('stock.levels.update');
  Route::get('/stock/levels/history', [StockLevelController::class, 'getStockHistory'])->name('stock.levels.history');
  Route::get('/stock/levels/alerts', [StockLevelController::class, 'getLowStockAlerts'])->name('stock.levels.alerts');
  Route::get('/stock/levels/export', [StockLevelController::class, 'exportStockLevels'])->name('stock.export-stock-csv');
  
  // Debug route (temporary)
  Route::get('/stock/debug', function() {
      $items = \App\Models\Item::where('is_active', true)->count();
      $currentStock = \App\Models\CurrentStock::count();
      $currentStockWithItems = \App\Models\CurrentStock::whereHas('item', function($q) {
          $q->where('is_active', true);
      })->count();
      
      return response()->json([
          'active_items' => $items,
          'current_stock_records' => $currentStock,
          'current_stock_with_active_items' => $currentStockWithItems,
          'sample_items' => \App\Models\Item::where('is_active', true)->limit(5)->get(['id', 'name', 'item_code', 'is_active']),
          'sample_current_stock' => \App\Models\CurrentStock::with('item')->limit(5)->get()
      ]);
  })->name('stock.debug');
  
  // Export Routes (matching Supervisor routes pattern)
  Route::get('/stock/export-csv', [StockLevelController::class, 'exportStockLevels'])->name('stock.export-stock-csv');
  Route::get('/stock/export-pdf', [StockLevelController::class, 'exportStockLevels'])->name('stock.export-stock-pdf');
  Route::get('/stock/print-report', [StockLevelController::class, 'index'])->name('stock.print-stock-report');
  Route::get('/stock/stock-card/{item}', [StockLevelController::class, 'stockCard'])->name('stock-card');
  
  // Stock Adjustments Routes
  Route::get('/stock/adjustments/items/{item}', [StockLevelController::class, 'getStockHistory'])->name('stock.adjustments.item-details');
  Route::post('/stock/adjustments', [StockLevelController::class, 'storeAdjustment'])->name('adjustments.store');


    // Notification routes
    Route::get('/notifications', [InventoryNotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/header', [InventoryNotificationController::class, 'getHeaderNotifications'])->name('notifications.header');
    Route::get('/notifications/unread-count', [InventoryNotificationController::class, 'getUnreadCount'])->name('notifications.unread_count');
    Route::get('/notifications/stats', [InventoryNotificationController::class, 'getNotificationStats'])->name('notifications.stats');
    Route::post('/notifications/mark-all-read', [InventoryNotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::post('/notifications/{notification}/mark-read', [InventoryNotificationController::class, 'markAsRead'])->name('notifications.mark_read');
    Route::post('/notifications/{notification}/mark-unread', [InventoryNotificationController::class, 'markAsUnread'])->name('notifications.mark_unread');
    Route::delete('/notifications/{notification}', [InventoryNotificationController::class, 'destroy'])->name('notifications.delete');
    Route::post('/notifications/bulk-operations', [InventoryNotificationController::class, 'bulkOperations'])->name('notifications.bulk_operations');

    // Sidebar badge count routes for real-time updates
    Route::get('/notifications/count', [GeneralController::class, 'getNotificationCount'])->name('notifications.count');
    Route::get('/requisitions/pending-count', [GeneralController::class, 'getPendingRequisitionsCount'])->name('requisitions.pending-count');

});





// 6. STAFF / EMPLOYEE ROUTES (Baker)
// Security: Only users with role 'employee' can access
// Note: AuthController redirects to 'employee.dashboard', so we name it 'employee.'
Route::middleware(['auth', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [EmployeeDashboardController::class, 'home'])->name('dashboard');

    // Requisitions
    Route::get('/requisitions/create', [EmployeeRequisitionController::class, 'showCreateRequisition'])->name('requisitions.create');
    Route::post('/requisitions', [EmployeeRequisitionController::class, 'createRequisition'])->name('requisitions.store');
    Route::get('/requisitions/history', [EmployeeRequisitionController::class, 'requisitionHistory'])->name('requisitions.history');
    Route::get('/requisitions/{requisition}/details', [EmployeeRequisitionController::class, 'getRequisitionDetails'])->name('requisitions.details');
    
    // Requisition Actions
    Route::post('/requisitions/{requisition}/confirm-receipt', [EmployeeRequisitionController::class, 'confirmReceipt'])->name('requisitions.confirm-receipt');

    // Production
    Route::get('/production/log', [EmployeeProductionController::class, 'productionLog'])->name('production.log');
    Route::post('/production/log', [EmployeeProductionController::class, 'storeProduction'])->name('production.store');
    Route::get('/recipes', [EmployeeRecipeController::class, 'recipes'])->name('recipes.index');
    Route::get('/recipes/{recipe}/details', [EmployeeRecipeController::class, 'getRecipeDetails'])->name('recipes.details');
    Route::post('/recipes', [EmployeeRecipeController::class, 'createRecipe'])->name('recipes.store');
    Route::put('/recipes/{recipe}', [EmployeeRecipeController::class, 'updateRecipe'])->name('recipes.update');
    Route::delete('/recipes/{recipe}', [EmployeeRecipeController::class, 'deleteRecipe'])->name('recipes.destroy');

    // Notifications
    Route::get('/notifications', [EmployeeNotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/header', [EmployeeNotificationController::class, 'getHeaderNotifications'])->name('notifications.header');
    Route::get('/notifications/unread-count', [EmployeeNotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{notification}/mark-read', [EmployeeNotificationController::class, 'markNotificationAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/{notification}/mark-unread', [EmployeeNotificationController::class, 'markNotificationAsUnread'])->name('notifications.mark-unread');
    Route::post('/notifications/mark-all-read', [EmployeeNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // AJAX endpoints
    Route::get('/items/search', [EmployeeRequisitionController::class, 'getItemsForRequisition'])->name('items.search');

});