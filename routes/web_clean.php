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

// This 'auth' middleware group REJECTS anyone who is not logged in.
Route::middleware(['auth'])->group(function () {

    // --- Admin Routes ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // Feature pages used by the Admin UI
        Route::get('/requisitions', [AdminController::class, 'requisitions'])->name('requisitions');
        Route::get('/item-requests', [AdminController::class, 'itemRequests'])->name('item-requests');
        Route::get('/inventory/transactions', [AdminController::class, 'inventoryTransactions'])->name('inventory-transactions');
        Route::get('/inventory/items', [AdminController::class, 'itemManagement'])->name('item-management');
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/reports/{report}', [AdminController::class, 'generateReport'])->name('reports.generate');
        Route::get('/purchase-orders', [AdminController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/suppliers', [AdminController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers', [AdminController::class, 'storeSupplier'])->name('suppliers.store');
        Route::get('/suppliers/{id}', [AdminController::class, 'showSupplier'])->name('suppliers.show');
        Route::put('/suppliers/{id}', [AdminController::class, 'updateSupplier'])->name('suppliers.update');
        Route::post('/suppliers/{id}/toggle', [AdminController::class, 'toggleSupplier'])->name('suppliers.toggle');
        Route::delete('/suppliers/{id}', [AdminController::class, 'deleteSupplier'])->name('suppliers.destroy');
        Route::get('/users', [AdminController::class, 'users'])->name('user-management');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::put('/users/{id}/password', [AdminController::class, 'changeUserPassword'])->name('users.password');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.destroy');
        Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/compose', [AdminController::class, 'composeNotificationPage'])->name('notifications.compose-page');

        // Notifications AJAX endpoints used by header
        Route::post('/notifications/{id}/mark-read', [AdminController::class, 'notificationMarkRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [AdminController::class, 'notificationMarkAllRead'])->name('notifications.mark-all');
        Route::get('/notifications/unread-count', [AdminController::class, 'notificationUnreadCount'])->name('notifications.unread-count');

        // Inventory/Items & Categories endpoints (JSON) used by modals
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::post('/items', [AdminController::class, 'storeItem'])->name('items.store');
        Route::get('/items/{id}', [AdminController::class, 'showItem'])->name('items.show');
        Route::put('/items/{id}', [AdminController::class, 'updateItem'])->name('items.update');
        Route::post('/items/{id}/stock', [AdminController::class, 'stockItem'])->name('items.stock');
        Route::delete('/items/{id}', [AdminController::class, 'deleteItem'])->name('items.destroy');

        // Requisitions details for modal
        Route::get('/requisitions/{id}', [AdminController::class, 'showRequisition'])->name('requisitions.show');

        // Notifications compose
        Route::post('/notifications/compose', [AdminController::class, 'composeNotification'])->name('notifications.compose');
    });

    // --- Staff (Employee) Routes ---
    Route::middleware('role:employee')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [StaffController::class, 'index'])->name('dashboard');

        // Requisitions (resource-style names)
        Route::get('/requisitions', [StaffController::class, 'requisitionsIndex'])->name('requisitions.index');
        Route::get('/requisitions/create', [StaffController::class, 'requisitionsCreate'])->name('requisitions.create');
        Route::post('/requisitions', [StaffController::class, 'requisitionsStore'])->name('requisitions.store');
        Route::get('/requisitions/{id}', [StaffController::class, 'requisitionsShow'])->name('requisitions.show');
        Route::get('/requisitions/{id}/edit', [StaffController::class, 'requisitionsEdit'])->name('requisitions.edit');
        Route::put('/requisitions/{id}', [StaffController::class, 'requisitionsUpdate'])->name('requisitions.update');
        Route::delete('/requisitions/{id}', [StaffController::class, 'requisitionsDestroy'])->name('requisitions.destroy');

        // Item Requests (resource-style names)
        Route::get('/item-requests', [StaffController::class, 'itemRequestsIndex'])->name('item-requests.index');
        Route::post('/item-requests', [StaffController::class, 'itemRequestsStore'])->name('item-requests.store');
        Route::get('/item-requests/{id}', [StaffController::class, 'itemRequestsShow'])->name('item-requests.show');
        Route::get('/item-requests/{id}/edit', [StaffController::class, 'itemRequestsEdit'])->name('item-requests.edit');
        Route::put('/item-requests/{id}', [StaffController::class, 'itemRequestsUpdate'])->name('item-requests.update');
        Route::post('/item-requests/cancel', [StaffController::class, 'itemRequestsCancel'])->name('item-requests.cancel');

        // Print requisition
        Route::get('/requisitions/{id}/print', [StaffController::class, 'printRequisition'])->name('requisitions.print');

        // AR
        Route::get('/ar', [StaffController::class, 'arIndex'])->name('ar');
        Route::post('/ar/confirm', [StaffController::class, 'arConfirm'])->name('acknowledgements.confirm');

        // Notifications
        Route::get('/notifications', [StaffController::class, 'notificationsIndex'])->name('notifications');
        Route::post('/notifications/mark-all-read', [StaffController::class, 'notificationsMarkAllRead'])->name('notifications.mark-all-read');
    });

    // --- Inventory Routes ---
    Route::middleware('role:inventory')->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/dashboard', [InventoryController::class, 'index'])->name('dashboard');
        
        // Dashboard API endpoints
        Route::get('/dashboard/stats', [InventoryController::class, 'dashboardStats'])->name('dashboard.stats');
        Route::get('/dashboard/expiry-alerts', [InventoryController::class, 'expiryAlerts'])->name('dashboard.expiry-alerts');
        Route::get('/dashboard/incoming-deliveries', [InventoryController::class, 'incomingDeliveries'])->name('dashboard.incoming-deliveries');
        Route::get('/dashboard/weekly-summary', [InventoryController::class, 'weeklyStockInSummary'])->name('dashboard.weekly-summary');
        
        // Items management routes
        Route::get('/items-list', [InventoryController::class, 'itemsList'])->name('items.list');
        Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
        Route::get('/items/{id}', [InventoryController::class, 'itemShow'])->name('items.show');
        Route::put('/items/{id}', [InventoryController::class, 'itemUpdate'])->name('items.update');
        Route::delete('/items/{id}', [InventoryController::class, 'itemDeactivate'])->name('items.destroy');
        Route::put('/items/bulk-update', [InventoryController::class, 'bulkUpdateItems'])->name('items.bulk-update');
        
        // Stock adjustment routes
        Route::get('/adjustments', [InventoryController::class, 'adjustmentsIndex'])->name('adjustments.index');
        Route::post('/adjustments', [InventoryController::class, 'storeAdjustment'])->name('adjustments.store');
        
        // Delivery routes
        Route::get('/deliveries/incoming', [InventoryController::class, 'deliveriesIndex'])->name('deliveries.incoming');
        Route::get('/deliveries/incoming/api', [InventoryController::class, 'deliveriesIncoming'])->name('deliveries.incoming.api');
        Route::get('/delivery/memo/{poId}', [InventoryController::class, 'deliveryMemo'])->name('delivery.memo');
        Route::post('/delivery/memo', [InventoryController::class, 'deliveryMemoStore'])->name('delivery.memo.store');
        
        // Stock-in processing routes
        Route::get('/stock-in', [InventoryController::class, 'stockInIndex'])->name('stock-in.index');
        Route::get('/stock-in/items/{poId}', [InventoryController::class, 'stockInItems'])->name('stock-in.items');
        Route::post('/stock-in/process', [InventoryController::class, 'stockInProcess'])->name('stock-in.process');
        
        // Stock-out processing routes
        Route::get('/stock-out', [InventoryController::class, 'stockOutIndex'])->name('stock-out.index');
        Route::get('/stock-out/requisitions', [InventoryController::class, 'stockOutRequisitions'])->name('stock-out.requisitions');
        Route::get('/stock-out/requisition-items/{reqId}', [InventoryController::class, 'stockOutRequisitionItems'])->name('stock-out.requisition-items');
        Route::post('/stock-out/process', [InventoryController::class, 'stockOutProcess'])->name('stock-out.process');
        
        // Transaction routes
        Route::get('/transactions', [InventoryController::class, 'transactionsIndex'])->name('transactions.index');
        Route::get('/transactions/list', [InventoryController::class, 'transactionsList'])->name('transactions.list');
        Route::get('/transactions/recent', [InventoryController::class, 'recentTransactions'])->name('transactions.recent');
        
        // Reports routes
        Route::get('/reports', [InventoryController::class, 'reportsIndex'])->name('reports');
        Route::get('/reports/low-stock', [InventoryController::class, 'lowStockReport'])->name('reports.low-stock');
        Route::get('/reports/expiry', [InventoryController::class, 'expiryReport'])->name('reports.expiry');
        Route::get('/reports/stock-card/{itemId}', [InventoryController::class, 'stockCardReport'])->name('reports.stock-card');
        
        // Notifications routes
        Route::get('/notifications', [InventoryController::class, 'notificationsIndex'])->name('notifications.index');
        Route::get('/notifications/{id}', [InventoryController::class, 'notificationShow'])->name('notifications.show');
        Route::post('/notifications/{id}/mark-read', [InventoryController::class, 'notificationMarkRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [InventoryController::class, 'notificationsMarkAllRead'])->name('notifications.markAllRead');
        
        // Other routes
        Route::get('/acknowledge-receipts', [InventoryController::class, 'acknowledgeReceiptsIndex'])->name('acknowledge-receipts.index');
        Route::get('/alerts', [InventoryController::class, 'alertsIndex'])->name('alerts.index');
        Route::get('/notifications/{id}/jump', [InventoryController::class, 'notificationsJump'])->name('notifications.jump');
        Route::post('/low-stock/notify', [InventoryController::class, 'notifyLowStock'])->name('low-stock.notify');
        Route::get('/approved-request-items', [InventoryController::class, 'approvedRequestItems'])->name('approved-request-items');
    });

    // --- Purchasing Routes ---
    Route::middleware('role:purchasing')->prefix('purchasing')->name('purchasing.')->group(function () {
        Route::get('/dashboard', [PurchasingController::class, 'index'])->name('dashboard');
        // Approved requisitions → Create PO
        Route::get('/approved-requisitions', [PurchasingController::class, 'approvedIndex'])->name('approved.index');
        Route::post('/requisitions/mark-read', [PurchasingController::class, 'requisitionsMarkRead'])->name('requisitions.markRead');
        // Suppliers (page + modal APIs)
        Route::get('/suppliers', [PurchasingController::class, 'suppliersIndex'])->name('suppliers.index');
        Route::post('/suppliers', [PurchasingController::class, 'supplierStore'])->name('suppliers.store');
        Route::get('/suppliers/{id}', [PurchasingController::class, 'supplierShow'])->name('suppliers.show');
        Route::put('/suppliers/{id}', [PurchasingController::class, 'supplierUpdate'])->name('suppliers.update');
        Route::get('/suppliers/{id}/pos', [PurchasingController::class, 'supplierPos'])->name('suppliers.pos');
        Route::post('/suppliers/{id}/toggle-status', [PurchasingController::class, 'supplierToggleStatus'])->name('suppliers.toggle');

        // Delivery recording
        Route::get('/delivery/{poId}', [PurchasingController::class, 'deliveryShow'])->name('delivery.show');
        Route::post('/delivery/{poId}', [PurchasingController::class, 'deliveryStore'])->name('delivery.store');
        // Create Purchase Order
        Route::post('/purchase-orders', [PurchasingController::class, 'purchaseOrdersStore'])->name('purchase-orders.store');
        // Memo pages
        Route::get('/memo', [PurchasingController::class, 'memoIndex'])->name('memo.index');
        Route::get('/memo/record', [PurchasingController::class, 'memoRecord'])->name('memo.record');
        Route::get('/memo/{memoRef}', [PurchasingController::class, 'memoShow'])->name('memo.show');
        // Reports
        Route::get('/reports', [PurchasingController::class, 'report'])->name('reports');
        Route::get('/reports/{report}', [PurchasingController::class, 'generateReport'])->name('reports.generate');
        // Notifications
        Route::get('/notifications', [PurchasingController::class, 'notificationsIndex'])->name('notifications');
        Route::get('/notifications/{id}', [PurchasingController::class, 'notificationsView'])->name('notifications.view');
        Route::post('/notifications/mark-all-read', [PurchasingController::class, 'notificationsMarkAllRead'])->name('notifications.markAllRead');
        Route::get('/notifications/{id}/jump', [PurchasingController::class, 'notificationsJump'])->name('notifications.jump');
        // Purchase create PO page
        Route::get('/purchase/create-po', [PurchasingController::class, 'purchaseCreatePo'])->name('purchase.create-po');
        // Purchase: create PO from multiple requisitions (JSON), view, print
        Route::post('/purchase/from-requisitions', [PurchasingController::class, 'purchaseCreateFromReqs'])->name('purchase.from-reqs');
        Route::get('/purchase/view/{id}', [PurchasingController::class, 'purchaseView'])->name('purchase.view');
        Route::post('/purchase/update/{id}', [PurchasingController::class, 'purchaseUpdate'])->name('purchase.update');
        Route::delete('/purchase/{id}', [PurchasingController::class, 'purchaseDestroy'])->name('purchase.destroy');
        Route::get('/purchase/print/{id}', [PurchasingController::class, 'purchasePrint'])->name('purchase.print');
        // Requisition details (JSON) for purchasing modals
        Route::get('/requisitions/{id}', [PurchasingController::class, 'requisitionShow'])->name('requisitions.show');
        // Reports
        Route::get('/report', [PurchasingController::class, 'report'])->name('report');
    });

    // --- Supervisor Routes ---
    Route::middleware('role:supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/dashboard', [SupervisorController::class, 'index'])->name('dashboard');

        // Requisitions review
        Route::get('/requisitions', [SupervisorController::class, 'requisitionsIndex'])->name('requisitions.index');
        Route::get('/requisitions/{id}', [SupervisorController::class, 'requisitionsShow'])->name('requisitions.show');
        Route::put('/requisitions/{id}/status', [SupervisorController::class, 'requisitionsUpdateStatus'])->name('requisitions.update-status');
        Route::post('/requisitions/{id}/status', [SupervisorController::class, 'requisitionsUpdateStatus'])->name('requisitions.update-status.post');

        // Item Requests approvals
        Route::get('/item-requests', [SupervisorController::class, 'itemRequestsIndex'])->name('item-requests.index');
        Route::get('/item-requests/{id}', [SupervisorController::class, 'itemRequestsShow'])->name('item-requests.show');
        Route::put('/item-requests/{id}/status', [SupervisorController::class, 'itemRequestsUpdateStatus'])->name('item-requests.update-status');
        Route::post('/item-requests/{id}/status', [SupervisorController::class, 'itemRequestsUpdateStatus'])->name('item-requests.update-status.post');

        // Inventory overview
        Route::get('/inventory/overview', [SupervisorController::class, 'inventoryOverview'])->name('inventory-overview');
        Route::get('/items/{id}', [SupervisorController::class, 'showItem'])->name('items.show');

        // Purchase Orders
        Route::get('/purchase-orders', [SupervisorController::class, 'purchaseOrdersIndex'])->name('purchase-orders');
        Route::put('/purchase-orders/{id}/approve', [SupervisorController::class, 'poApprove'])->name('po.approve');
        Route::put('/purchase-orders/{id}/reject', [SupervisorController::class, 'poReject'])->name('po.reject');

        // Notifications
        Route::get('/notifications', [SupervisorController::class, 'notificationsIndex'])->name('notifications');
        Route::post('/notifications/mark-all-read', [SupervisorController::class, 'notificationsMarkAllRead'])->name('notifications.markAllRead');

        // Reports
        Route::get('/reports', [SupervisorController::class, 'reportsIndex'])->name('reports');
        Route::get('/reports/print/requisition', [SupervisorController::class, 'printRequisition'])->name('reports.print.requisition');
        Route::get('/reports/print/item-request', [SupervisorController::class, 'printItemRequest'])->name('reports.print.item-request');
        Route::get('/reports/print/purchase-order', [SupervisorController::class, 'printPurchaseOrder'])->name('reports.print.purchase-order');
        Route::get('/reports/print/inventory-health', [SupervisorController::class, 'printInventoryHealth'])->name('reports.print.inventory-health');
    });
    
});