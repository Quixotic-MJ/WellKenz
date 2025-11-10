<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;        // NEW
use Illuminate\Support\Facades\DB;

/* ----------------------------------------------------------
   PUBLIC ROUTES
---------------------------------------------------------- */

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/* ----------------------------------------------------------
   AUTHENTICATED COMMON ROUTES
---------------------------------------------------------- */
Route::middleware(['auth'])->group(function () {

    /* ---------- Item ---------- */
    Route::prefix('items')->group(function () {
        Route::get('/requisition', [ItemController::class, 'getItemsForRequisition'])->name('items.requisition');
        Route::get('/category/{categoryId}', [ItemController::class, 'getItemsByCategory'])->name('items.by_category');
        Route::get('/search', [ItemController::class, 'searchItems'])->name('items.search');
        Route::get('/low-stock', [ItemController::class, 'getLowStock'])->name('items.low_stock');
        Route::get('/{id}', [ItemController::class, 'getItemDetails'])->name('items.details');
        Route::get('/inventory/items', [RequisitionController::class, 'getInventoryItems'])->name('items.inventory');
    });

    /* ---------- Item Request ---------- */
    Route::prefix('item-requests')->group(function () {
        Route::get('/create', [ItemRequestController::class, 'create'])->name('item_requests.create');
        Route::post('/', [ItemRequestController::class, 'store'])->name('item_requests.store');
        Route::get('/my-requests', [ItemRequestController::class, 'getMyRequests'])->name('item_requests.my_requests');
        Route::get('/{id}', [ItemRequestController::class, 'show'])->name('item_requests.show');

        Route::get('/pending', [ItemRequestController::class, 'getPendingRequests'])->name('item_requests.pending');
        Route::post('/{id}/approve', [ItemRequestController::class, 'approve'])->name('item_requests.approve');
        Route::post('/{id}/reject', [ItemRequestController::class, 'reject'])->name('item_requests.reject');
    });

    /* ---------- Notification ---------- */
    Route::prefix('notifications')->group(function () {
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');
    });

    /* ---------- Requisition ---------- */
    Route::prefix('requisitions')->group(function () {
        Route::get('/create', [RequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('/', [RequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('/my-requisitions', [RequisitionController::class, 'getMyRequisitions'])->name('requisitions.my_requisitions');
        Route::get('/{id}', [RequisitionController::class, 'getRequisitionDetails'])->name('requisitions.show');
        Route::post('/{id}/status', [RequisitionController::class, 'updateStatus'])->name('requisitions.update_status');
        Route::delete('/{id}', [RequisitionController::class, 'destroy'])->name('requisitions.destroy');
        Route::get('/', [RequisitionController::class, 'getAllRequisitions'])->name('requisitions.index');
    });

    /* ----------------------------------------------------------
       ROLE-BASED DASHBOARDS & SCREENS
    ---------------------------------------------------------- */

    /* ---------- Admin ---------- */
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', fn() => view('Admin.dashboard'))->name('Admin_dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('Admin_User_Management');
        Route::get('/item-request', fn() => view('Admin.Requisition.item_request'))->name('Admin_Item_Request');
        Route::get('/requisition', fn() => view('Admin.Requisition.requisition'))->name('Admin_Requisition');
        Route::get('/purchase-order', fn() => view('Admin.Purchasing.purchase'))->name('Admin_Purchase_Order');
        Route::get('/supplier', fn() => view('Admin.Purchasing.supplier'))->name('Admin_Supplier');
        Route::get('/inventory-transaction', fn() => view('Admin.Inventory.inventory_transaction'))->name('Admin_Inventory_Transaction');
        Route::get('/inventory', fn() => view('Admin.Inventory.inventory'))->name('Admin_Inventory');
        Route::get('/report', fn() => view('Admin.report'))->name('Admin_Report');
        Route::get('/notification', fn() => view('Admin.notification'))->name('Admin_Notification');

        /* User CRUD */
        Route::prefix('users')->group(function () {
            Route::post('/', [UserController::class, 'store'])->name('users.store');
            Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::post('/{id}', [UserController::class, 'update'])->name('users.update');
            Route::post('/{id}/password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
            Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
            Route::get('/search', [UserController::class, 'search'])->name('users.search');
        });
    });

    /* ---------- Employee ---------- */
    Route::middleware(['role:employee'])->group(function () {
        Route::get('/Staff_dashboard', fn() => view('Employee.dashboard'))->name('Staff_dashboard');
        Route::get('/Staff_Create_Requisition', [RequisitionController::class, 'create'])->name('Staff_Create_Requisition');
        Route::get('/Staff_Requisition_Record', fn() => view('Employee.Requisition.my_requisition'))->name('Staff_Requisition_Record');
        Route::get('/Staff_Item_Request', [ItemRequestController::class, 'create'])->name('Staff_Item_Request');
        Route::get('/Staff_Receipt', fn() => view('Employee.acknowledgement_receipt'))->name('Staff_Reciept');
        Route::get('/Staff_Notification', fn() => view('Employee.notification'))->name('Staff_Notification');
    });

    /* ---------- Inventory ---------- */
    Route::middleware(['role:inventory'])->group(function () {
        Route::get('/Inventory_Dashboard', fn() => view('Inventory.dashboard'))->name('Inventory_Dashboard');
        Route::get('/Inventory_List', fn() => view('Inventory.inventory_list'))->name('Inventory_List');
        Route::get('/Inventory_Low_Stock_Alert', fn() => view('Inventory.low_stock_alert'))->name('Inventory_Low_Stock_Alert_notification');
        Route::get('/Inventory_Notification', fn() => view('Inventory.notification'))->name('Inventory_Notification');
        Route::get('/Inventory_Report', fn() => view('Inventory.report'))->name('Inventory_Report');
        Route::get('/Inventory_Stock_in', fn() => view('Inventory.stock_in'))->name('Inventory_Stock_in');
        Route::get('/Inventory_Stock_out', fn() => view('Inventory.stock_out'))->name('Inventory_Stock_out');
    });

    /* ---------- Purchasing ---------- */
    Route::middleware(['role:purchasing'])->group(function () {
        /* Dashboard */
        Route::get('/Purchasing_dashboard', [PurchaseOrderController::class, 'dashboard'])->name('Purchasing_dashboard');

        /* Screens */
        Route::get('/Purchasing_Purchase_Order', fn() => view('Purchasing.create_purchase_order'))->name('Purchasing_Purchase_Order');
        Route::get('/Purchasing_Approved_Requisition', fn() => view('Purchasing.approved_requisition'))->name('Purchasing_Approved_Requisition');
        Route::get('/Purchasing_Inventory_overview', fn() => view('Purchasing.inventory_overview'))->name('Purchasing_Inventory_overview');
        Route::get('/Purchasing_Notification', fn() => view('Purchasing.notification'))->name('Purchasing_Notification');
        Route::get('/Purchasing_Report', fn() => view('Purchasing.report'))->name('Purchasing_Report');
        Route::get('/Purchasing_Supplier', fn() => view('Purchasing.supplier'))->name('Purchasing_Supplier');

        /* Purchase Order Resource */
        Route::prefix('purchase-orders')->group(function () {
            Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase_orders.index');
            Route::get('/create/{requisition}', [PurchaseOrderController::class, 'createFromRequisition'])->name('purchase_orders.create');
            Route::post('/', [PurchaseOrderController::class, 'store'])->name('purchase_orders.store');
            Route::get('/print/{po}', [PurchaseOrderController::class, 'print'])->name('purchase_orders.print');
        });

        /* Supplier AJAX CRUD */
        Route::prefix('purchasing/supplier')->name('supplier.')->controller(SupplierController::class)->group(function () {
            Route::post('/', 'store')->name('store');      // <-- ADD ->name('store')
            Route::put('/{supplier}', 'update')->name('update');
            Route::delete('/{supplier}', 'destroy')->name('destroy');
        });
    });

    /* ---------- Supervisor ---------- */
    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('/Supervisor_Dashboard', fn() => view('Supervisor.dashboard'))->name('Supervisor_Dashboard');
        Route::get('/Supervisor_Requisition', fn() => view('Supervisor.Requisition.requisition'))->name('Supervisor_Requisition');
        Route::get('/Supervisor_Item_Request', fn() => view('Supervisor.Requisition.item_request'))->name('Supervisor_Item_Request');
        Route::get('/Supervisor_Purchase_Order', fn() => view('Supervisor.purchase_order'))->name('Supervisor_Purchase_Order');
        Route::get('/Supervisor_Inventory_Overview', fn() => view('Supervisor.inventory_overview'))->name('Supervisor_Inventory_Overview');
        Route::get('/Supervisor_Report', fn() => view('Supervisor.report'))->name('Supervisor_Report');
        Route::get('/Supervisor_Notification', fn() => view('Supervisor.notification'))->name('Supervisor_Notification');

        /* Supervisor API helpers */
        Route::prefix('supervisor')->group(function () {
            Route::get('/requisitions', [RequisitionController::class, 'getRequisitionsForApproval'])->name('supervisor.requisitions.index');
            Route::get('/requisitions/stats', [RequisitionController::class, 'getRequisitionStats'])->name('supervisor.requisitions.stats');
            Route::get('/requisitions/{id}', [RequisitionController::class, 'getRequisitionForReview'])->name('supervisor.requisitions.show');
            Route::post('/requisitions/{id}/status', [RequisitionController::class, 'updateRequisitionStatus'])->name('supervisor.requisitions.status');

            Route::get('/item-requests', [ItemRequestController::class, 'getAllRequests'])->name('supervisor.item_requests.index');
            Route::get('/item-requests/stats', [ItemRequestController::class, 'getRequestStats'])->name('supervisor.item_requests.stats');
            Route::get('/item-requests/{id}', [ItemRequestController::class, 'show'])->name('supervisor.item_requests.show');
            Route::post('/item-requests/{id}/status', [ItemRequestController::class, 'updateStatus'])->name('supervisor.item_requests.update_status');
        });
    });
});

/* ----------------------------------------------------------
   API END-POINTS (AJAX) – AUTHENTICATED
---------------------------------------------------------- */
Route::middleware(['auth'])->prefix('api')->group(function () {
    /* Item */
    Route::get('/items/requisition', [ItemController::class, 'getItemsForRequisition']);
    Route::get('/items/{id}', [ItemController::class, 'getItemDetails']);
    Route::get('/items/category/{categoryId}', [ItemController::class, 'getItemsByCategory']);
    Route::get('/items/search', [ItemController::class, 'searchItems']);

    /* Requisition */
    Route::post('/requisitions', [RequisitionController::class, 'store']);
    Route::get('/requisitions/my', [RequisitionController::class, 'getMyRequisitions']);
    Route::get('/requisitions/all', [RequisitionController::class, 'getAllRequisitions']);
    Route::get('/requisitions/{id}', [RequisitionController::class, 'getRequisitionDetails']);
    Route::post('/requisitions/{id}/status', [RequisitionController::class, 'updateStatus']);
    Route::delete('/requisitions/{id}', [RequisitionController::class, 'destroy']);

    /* Item Request */
    Route::post('/item-requests', [ItemRequestController::class, 'store']);
    Route::get('/item-requests/my', [ItemRequestController::class, 'getMyRequests']);
    Route::get('/item-requests/{id}', [ItemRequestController::class, 'show']);
    Route::get('/item-requests/pending', [ItemRequestController::class, 'getPendingRequests']);
    Route::post('/item-requests/{id}/approve', [ItemRequestController::class, 'approve']);
    Route::post('/item-requests/{id}/reject', [ItemRequestController::class, 'reject']);

    /* Inventory */
    Route::get('/inventory/items', [RequisitionController::class, 'getInventoryItems']);
});
