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

    // --- Admin Routes ---
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        /* -----  Requisitions / Item-requests  ----- */
        Route::get('/requisitions', [AdminController::class, 'requisitions'])->name('requisitions');
        Route::get('/requisitions/{id}', [AdminController::class, 'showRequisition'])->name('requisitions.show');
        Route::post('/requisitions/{id}/status', [AdminController::class, 'updateRequisitionStatus'])->name('requisitions.status');

        Route::get('/item-requests', [AdminController::class, 'itemRequests'])->name('item-requests');
        Route::post('/item-requests/{id}/status', [AdminController::class, 'updateItemRequestStatus'])->name('item-requests.status');

        /* -----  Inventory  ----- */
        Route::get('/inventory/transactions', [AdminController::class, 'inventoryTransactions'])->name('inventory-transactions');
        Route::get('/inventory/transactions/{id}', [AdminController::class, 'transactionShow'])->name('inventory-transactions.show');
        Route::get('/inventory/items', [AdminController::class, 'itemManagement'])->name('item-management');

        /* -----  Items & Categories (JSON)  ----- */
        Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::post('/items', [AdminController::class, 'storeItem'])->name('items.store');
        Route::get('/items/{id}', [AdminController::class, 'showItem'])->name('items.show');
        Route::put('/items/{id}', [AdminController::class, 'updateItem'])->name('items.update');
        Route::post('/items/{id}/stock', [AdminController::class, 'stockItem'])->name('items.stock');
        Route::delete('/items/{id}', [AdminController::class, 'deleteItem'])->name('items.destroy');

        /* -----  Purchase Orders  ----- */
        Route::get('/purchase-orders', [AdminController::class, 'purchaseOrders'])->name('purchase-orders');
        Route::get('/purchase-orders/{id}', [AdminController::class, 'purchaseOrderShow'])->name('purchase-orders.show');
        Route::post('/purchase-orders/{id}/status', [AdminController::class, 'purchaseOrderStatusUpdate'])->name('purchase-orders.status');

        /* -----  Suppliers  ----- */
        Route::get('/suppliers', [AdminController::class, 'suppliers'])->name('suppliers');
        Route::post('/suppliers', [AdminController::class, 'storeSupplier'])->name('suppliers.store');
        Route::get('/suppliers/{id}', [AdminController::class, 'showSupplier'])->name('suppliers.show');
        Route::put('/suppliers/{id}', [AdminController::class, 'updateSupplier'])->name('suppliers.update');
        Route::post('/suppliers/{id}/toggle', [AdminController::class, 'toggleSupplier'])->name('suppliers.toggle');
        Route::delete('/suppliers/{id}', [AdminController::class, 'deleteSupplier'])->name('suppliers.destroy');

        /* -----  Users  ----- */
        Route::get('/users', [AdminController::class, 'users'])->name('user-management');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('users.show');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::put('/users/{id}/password', [AdminController::class, 'changeUserPassword'])->name('users.password');
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('users.destroy');

        /* -----  Reports  ----- */
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
        Route::get('/reports/{report}', [AdminController::class, 'generateReport'])->name('reports.generate');

        /* -----  Acknowledge Receipts  ----- */
        Route::get('/acknowledge-receipts', [AdminController::class, 'acknowledgeReceipts'])->name('acknowledge-receipts.index');
        Route::get('/acknowledge-receipts/{id}', [AdminController::class, 'showAcknowledgeReceipt'])->name('acknowledge-receipts.show');

        /* -----  Memos  ----- */
        Route::get('/memos', [AdminController::class, 'memos'])->name('memos.index');
        Route::get('/memos/{id}', [AdminController::class, 'showMemo'])->name('memos.show');

        /* -----  Notifications  ----- */
        Route::get('/notifications', [AdminController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/compose', [AdminController::class, 'composeNotificationPage'])->name('notifications.compose-page');
        Route::post('/notifications/compose', [AdminController::class, 'composeNotification'])->name('notifications.compose');
        Route::post('/notifications/{id}/mark-read', [AdminController::class, 'notificationMarkRead'])->name('notifications.mark-read');
        Route::post('/notifications/mark-all-read', [AdminController::class, 'notificationMarkAllRead'])->name('notifications.mark-all');
        Route::get('/notifications/unread-count', [AdminController::class, 'notificationUnreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/{id}', [AdminController::class, 'notificationShow'])->name('notifications.show');
    });

    /* ----------------  STAFF (employee)  ---------------- */
    Route::middleware('role:employee')
        ->prefix('staff')
        ->name('staff.')
        ->group(function () {

            Route::get('/dashboard', [StaffController::class, 'index'])->name('dashboard');

            /* ---  Requisitions  --- */
            Route::resource('requisitions', StaffController::class)
                ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
                ->names([
                    'index' => 'requisitions.index',
                    'create' => 'requisitions.create',
                    'store' => 'requisitions.store',
                    'show' => 'requisitions.show',
                    'edit' => 'requisitions.edit',
                    'update' => 'requisitions.update',
                    'destroy' => 'requisitions.destroy',
                ]);
            Route::get('/requisitions/{id}/print', [StaffController::class, 'printRequisition'])->name('requisitions.print');

            /* ---  Item Requests  --- */
            Route::get('/item-requests', [StaffController::class, 'itemRequestsIndex'])->name('item-requests.index');
            Route::post('/item-requests', [StaffController::class, 'itemRequestsStore'])->name('item-requests.store');
            Route::get('/item-requests/{id}', [StaffController::class, 'itemRequestsShow'])->name('item-requests.show');
            Route::get('/item-requests/{id}/edit', [StaffController::class, 'itemRequestsEdit'])->name('item-requests.edit');
            Route::put('/item-requests/{id}', [StaffController::class, 'itemRequestsUpdate'])->name('item-requests.update');
            Route::post('/item-requests/cancel', [StaffController::class, 'itemRequestsCancel'])->name('item-requests.cancel');

            /* ---  Acknowledge Receipts  --- */
            Route::get('/ar', [StaffController::class, 'arIndex'])->name('ar');
            Route::get('/ar/{id}', [StaffController::class, 'arShow'])->name('ar.show');
            Route::get('/ar/{id}/print', [StaffController::class, 'arPrint'])->name('ar.print');
            Route::post('/ar/confirm', [StaffController::class, 'arConfirm'])->name('acknowledgements.confirm');

            /* ---  Notifications  --- */
            Route::get('/notifications', [StaffController::class, 'notificationsIndex'])->name('notifications');
            // ***** FIX: ADDED MISSING ROUTES *****
            Route::get('/notifications/{id}', [StaffController::class, 'notificationShow'])->name('notifications.show');
            Route::post('/notifications/{id}/mark-read', [StaffController::class, 'notificationMarkRead'])->name('notifications.mark-read');
            Route::post('/notifications/mark-all-read', [StaffController::class, 'notificationsMarkAllRead'])->name('notifications.mark-all-read');
        });

    /* ----------------  INVENTORY  ---------------- */
    Route::middleware('role:inventory')
        ->prefix('inventory')
        ->name('inventory.')
        ->group(function () {
            
            // Dashboard routes
            Route::get('/dashboard', [InventoryController::class, 'index'])->name('dashboard');
            Route::get('/dashboard/stats', [InventoryController::class, 'dashboardStats'])->name('dashboard.stats');
            Route::get('/dashboard/expiry-alerts', [InventoryController::class, 'expiryAlerts'])->name('dashboard.expiry-alerts');
            Route::get('/dashboard/incoming-deliveries', [InventoryController::class, 'incomingDeliveries'])->name('dashboard.incoming-deliveries');
            Route::get('/dashboard/weekly-summary', [InventoryController::class, 'weeklyStockInSummary'])->name('dashboard.weekly-summary');

            // Transaction routes
            Route::get('/transactions', [InventoryController::class, 'transactionsIndex'])->name('transactions.index');
            Route::get('/transactions/index', [InventoryController::class, 'transactionsIndex'])->name('transactions.index');

            // Item management routes
            Route::get('/items/list', [InventoryController::class, 'itemsList'])->name('items.list');
            Route::get('/items/list/data', [InventoryController::class, 'itemsListData'])->name('items.list.data');
            Route::get('/items/{id}', [InventoryController::class, 'itemShow'])->name('items.show');
            Route::post('/items', [InventoryController::class, 'storeItem'])->name('items.store');
            Route::put('/items/{id}', [InventoryController::class, 'itemUpdate'])->name('items.update');
            Route::delete('/items/{id}', [InventoryController::class, 'itemDeactivate'])->name('items.destroy');
            Route::post('/items/adjustment', [InventoryController::class, 'storeAdjustment'])->name('items.adjustment');

            // Delivery routes
            Route::get('/deliveries/incoming', [InventoryController::class, 'deliveriesIndex'])->name('deliveries.incoming');

            // Stock operations routes
            Route::get('/stock-in', [InventoryController::class, 'stockInIndex'])->name('stock-in.index');
            Route::get('/stock-out', [InventoryController::class, 'stockOutIndex'])->name('stock-out.index');
            Route::get('/adjustments', [InventoryController::class, 'adjustmentsIndex'])->name('adjustments.index');
            Route::post('/adjustments', [InventoryController::class, 'storeAdjustment'])->name('adjustments.store');

            // Monitoring routes
            Route::get('/alerts', [InventoryController::class, 'alertsIndex'])->name('alerts.index');

            // Stock processing API routes
            Route::get('/stock-in/items/{poId}', [InventoryController::class, 'stockInItems'])->name('stock-in.items');
            Route::post('/stock-in/process', [InventoryController::class, 'stockInProcess'])->name('stock-in.process');
            Route::get('/stock-out/requisitions', [InventoryController::class, 'stockOutRequisitions'])->name('stock-out.requisitions');
            Route::get('/stock-out/requisition-items/{reqId}', [InventoryController::class, 'stockOutRequisitionItems'])->name('stock-out.requisition-items');
            Route::post('/stock-out/process', [InventoryController::class, 'stockOutProcess'])->name('stock-out.process');
            Route::get('/deliveries/memo/{poId}', [InventoryController::class, 'deliveryMemo'])->name('deliveries.memo');
            Route::post('/deliveries/memo', [InventoryController::class, 'deliveryMemoStore'])->name('deliveries.memo.store');
            Route::get('/deliveries/incoming-data', [InventoryController::class, 'deliveriesIncoming'])->name('deliveries.incoming-data');

            // Transaction API routes
            Route::get('/transactions/data', [InventoryController::class, 'transactionsList'])->name('transactions.list');
            Route::get('/transactions/{id}', [InventoryController::class, 'transactionShow'])->name('transactions.show');

            // Acknowledgement receipts routes
            Route::get('/acknowledge-receipts', [InventoryController::class, 'acknowledgeReceiptsIndex'])->name('acknowledge-receipts.index');
            Route::get('/acknowledge-receipts/{id}/view', [InventoryController::class, 'acknowledgeReceiptView'])->name('acknowledge-receipts.view');
            Route::get('/acknowledge-receipts/{id}/print', [InventoryController::class, 'acknowledgeReceiptPrint'])->name('acknowledge-receipts.print');
            Route::get('/acknowledge-receipts/export', [InventoryController::class, 'acknowledgeReceiptExport'])->name('acknowledge-receipts.export');

            // Reports routes
            Route::get('/reports', [InventoryController::class, 'reportsIndex'])->name('reports');
            Route::get('/reports/low-stock', [InventoryController::class, 'lowStockReport'])->name('reports.low-stock');
            Route::get('/reports/expiry', [InventoryController::class, 'expiryReport'])->name('reports.expiry');
            Route::get('/reports/stock-card/{itemId}', [InventoryController::class, 'stockCardReport'])->name('reports.stock-card');

            // Utility API routes
            Route::get('/recent-transactions', [InventoryController::class, 'recentTransactions'])->name('recent-transactions');
            Route::post('/bulk-update-items', [InventoryController::class, 'bulkUpdateItems'])->name('bulk-update-items');
            Route::post('/bulk-stock-in', [InventoryController::class, 'storeBulkStockIn'])->name('bulk-stock-in');

            // Notifications routes
            Route::get('/notifications', [InventoryController::class, 'notificationsIndex'])->name('notifications.index');
            Route::get('/notifications/{id}', [InventoryController::class, 'notificationShow'])->name('notifications.show');
            Route::post('/notifications/{id}/mark-read', [InventoryController::class, 'notificationMarkRead'])->name('notifications.mark-read');
            Route::post('/notifications/mark-all-read', [InventoryController::class, 'notificationsMarkAllRead'])->name('notifications.mark-all-read');
            Route::get('/notifications/{id}/jump', [InventoryController::class, 'notificationsJump'])->name('notifications.jump');
        });

    /* ----------------  PURCHASING  ---------------- */
    Route::middleware('role:purchasing')
        ->prefix('purchasing')
        ->name('purchasing.')
        ->group(function () {

            Route::get('/dashboard', [PurchasingController::class, 'index'])->name('dashboard');

            /* --- Purchase Order Creation (from Reqs) --- */
            Route::get('/approved-requisitions', [PurchasingController::class, 'approvedIndex'])->name('approved.index');
            
            // ***** NEW ROUTE FOR VIEW MODAL *****
            Route::get('/requisitions/{id}', [PurchasingController::class, 'requisitionsShow'])->name('requisitions.show');

            Route::post('/purchase/from-reqs', [PurchasingController::class, 'purchaseFromReqs'])->name('purchase.from-reqs');
            Route::get('/purchase/view/{id}', [PurchasingController::class, 'purchaseView'])->name('purchase.view');
            Route::post('/purchase/update/{id}', [PurchasingController::class, 'purchaseUpdate'])->name('purchase.update');
            Route::delete('/purchase/destroy/{id}', [PurchasingController::class, 'purchaseDestroy'])->name('purchase.destroy');
            Route::get('/purchase/print/{id}', [PurchasingController::class, 'purchasePrint'])->name('purchase.print');


            /* --- Supplier Management --- */
            Route::get('/suppliers', [PurchasingController::class, 'suppliersIndex'])->name('suppliers.index');
            Route::post('/suppliers', [PurchasingController::class, 'suppliersStore'])->name('suppliers.store');
            Route::get('/suppliers/{id}', [PurchasingController::class, 'suppliersShow'])->name('suppliers.show');
            Route::put('/suppliers/{id}', [PurchasingController::class, 'suppliersUpdate'])->name('suppliers.update');
            Route::post('/suppliers/{id}/toggle-status', [PurchasingController::class, 'suppliersToggleStatus'])->name('suppliers.toggle-status');
            Route::get('/suppliers/{id}/pos', [PurchasingController::class, 'suppliersPOs'])->name('suppliers.pos');

            
            /* --- Delivery Memos --- */
            Route::get('/memo', [PurchasingController::class, 'memoIndex'])->name('memo.index');
            Route::get('/memo/{ref}', [PurchasingController::class, 'memoShow'])->name('memo.show');
            Route::get('/delivery/{id}', [PurchasingController::class, 'deliveryShow'])->name('delivery.show'); 
            Route::post('/delivery/{id}', [PurchasingController::class, 'deliveryStore'])->name('delivery.store'); 


            /* --- Reports --- */
            Route::get('/report', [PurchasingController::class, 'reportIndex'])->name('report');
            Route::get('/reports/{type}', [PurchasingController::class, 'reportGenerate'])->name('reports.generate');
            Route::get('/reports/{type}/print', [PurchasingController::class, 'reportPrint'])->name('reports.print');


            /* --- Notifications --- */
            Route::get('/notifications', [PurchasingController::class, 'notificationsIndex'])->name('notifications');
            Route::get('/notifications/{id}', [PurchasingController::class, 'notificationsView'])->name('notifications.view');
            Route::post('/notifications/{id}/mark-read', [PurchasingController::class, 'notificationsMarkRead'])->name('notifications.mark-read');
            Route::post('/notifications/mark-all-read', [PurchasingController::class, 'notificationsMarkAllRead'])->name('notifications.markAllRead');
            Route::get('/notifications/jump/{id}', [PurchasingController::class, 'notificationsJump'])->name('notifications.jump');
        });

    /* ----------------  SUPERVISOR  ---------------- */
    Route::middleware('role:supervisor')
        ->prefix('supervisor')
        ->name('supervisor.')
        ->group(function () {

            Route::get('/dashboard', [SupervisorController::class, 'index'])->name('dashboard');

            /* ---  Requisitions  --- */
            Route::get('/requisitions', [SupervisorController::class, 'requisitionsIndex'])->name('requisitions.index');
            Route::get('/requisitions/{id}', [SupervisorController::class, 'requisitionsShow'])->name('requisitions.show');
            Route::put('/requisitions/{id}/status', [SupervisorController::class, 'requisitionsUpdateStatus'])->name('requisitions.update-status');
            Route::post('/requisitions/{id}/status', [SupervisorController::class, 'requisitionsUpdateStatus'])->name('requisitions.update-status.post');

            /* ---  Item Requests  --- */
            Route::get('/item-requests', [SupervisorController::class, 'itemRequestsIndex'])->name('item-requests.index');
            Route::get('/item-requests/{id}', [SupervisorController::class, 'itemRequestsShow'])->name('item-requests.show');
            Route::put('/item-requests/{id}/status', [SupervisorController::class, 'itemRequestsUpdateStatus'])->name('item-requests.update-status');
            Route::post('/item-requests/{id}/status', [SupervisorController::class, 'itemRequestsUpdateStatus'])->name('item-requests.update-status.post');

            /* ---  Inventory  --- */
            Route::get('/inventory/overview', [SupervisorController::class, 'inventoryOverview'])->name('inventory-overview');
            Route::get('/items/{id}', [SupervisorController::class, 'showItem'])->name('items.show');

            /* ---  Purchase Orders  --- */
            Route::get('/purchase-orders', [SupervisorController::class, 'purchaseOrdersIndex'])->name('purchase-orders');
            Route::get('/purchase-orders/{id}', [SupervisorController::class, 'purchaseOrdersShow'])->name('purchase-orders.show');
            Route::put('/purchase-orders/{id}/approve', [SupervisorController::class, 'poApprove'])->name('po.approve');
            Route::put('/purchase-orders/{id}/reject', [SupervisorController::class, 'poReject'])->name('po.reject');

            /* ---  Notifications  --- */
            Route::get('/notifications', [SupervisorController::class, 'notificationsIndex'])->name('notifications');
            Route::post('/notifications/mark-all-read', [SupervisorController::class, 'notificationsMarkAllRead'])->name('notifications.markAllRead');

            /* ---  Reports / Prints  --- */
            Route::get('/reports', [SupervisorController::class, 'reportsIndex'])->name('reports');
            
            // ***** FIX: REPLACED OLD PRINT ROUTES WITH A SINGLE GENERATE ROUTE *****
            Route::get('/reports/generate/{report}', [SupervisorController::class, 'generateReport'])->name('reports.generate');
            
            // (Leaving old routes in case they are linked elsewhere, but they are no longer used by the report page)
            Route::get('/reports/print/requisition', [SupervisorController::class, 'printRequisition'])->name('reports.print.requisition');
            Route::get('/reports/print/item-request', [SupervisorController::class, 'printItemRequest'])->name('reports.print.item-request');
            Route::get('/reports/print/purchase-order', [SupervisorController::class, 'printPurchaseOrder'])->name('reports.print.purchase-order');
            Route::get('/reports/print/inventory-health', [SupervisorController::class, 'printInventoryHealth'])->name('reports.print.inventory-health');
        });
});

