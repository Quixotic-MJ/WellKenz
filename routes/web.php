<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemRequestController;
use Illuminate\Support\Facades\DB;

// Public routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Role-based middleware groups
Route::middleware(['auth'])->group(function () {
    // Common routes accessible to all authenticated users
    Route::prefix('items')->group(function () {
        Route::get('/requisition', [ItemController::class, 'getItemsForRequisition'])->name('items.requisition');
        Route::get('/category/{categoryId}', [ItemController::class, 'getItemsByCategory'])->name('items.by_category');
        Route::get('/search', [ItemController::class, 'searchItems'])->name('items.search');
        Route::get('/low-stock', [ItemController::class, 'getLowStock'])->name('items.low_stock');
        Route::get('/{id}', [ItemController::class, 'getItemDetails'])->name('items.details');
        Route::get('/inventory/items', [RequisitionController::class, 'getInventoryItems'])->name('items.inventory');
    });

    // Item Request Routes - Updated with complete CRUD
    Route::prefix('item-requests')->group(function () {
        // Create and view
        Route::get('/create', [ItemRequestController::class, 'create'])->name('item_requests.create');
        Route::post('/', [ItemRequestController::class, 'store'])->name('item_requests.store');
        Route::get('/my-requests', [ItemRequestController::class, 'getMyRequests'])->name('item_requests.my_requests');
        Route::get('/{id}', [ItemRequestController::class, 'show'])->name('item_requests.show');

        // Approval routes (for supervisors/admins)
        Route::get('/pending', [ItemRequestController::class, 'getPendingRequests'])->name('item_requests.pending');
        Route::post('/{id}/approve', [ItemRequestController::class, 'approve'])->name('item_requests.approve');
        Route::post('/{id}/reject', [ItemRequestController::class, 'reject'])->name('item_requests.reject');
    });

    // Notification routes
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unreadCount');

    // Requisition Routes
    Route::prefix('requisitions')->group(function () {
        // Create and store requisitions
        Route::get('/create', [RequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('/', [RequisitionController::class, 'store'])->name('requisitions.store');

        // View requisitions
        Route::get('/my-requisitions', [RequisitionController::class, 'getMyRequisitions'])->name('requisitions.my_requisitions');
        Route::get('/{id}', [RequisitionController::class, 'getRequisitionDetails'])->name('requisitions.show');

        // Management routes
        Route::post('/{id}/status', [RequisitionController::class, 'updateStatus'])->name('requisitions.update_status');
        Route::delete('/{id}', [RequisitionController::class, 'destroy'])->name('requisitions.destroy');

        // Admin/Supervisor routes
        Route::get('/', [RequisitionController::class, 'getAllRequisitions'])->name('requisitions.index');
    });

    // Supervisor Requisition Routes
    Route::prefix('supervisor')->middleware(['auth'])->group(function () {
        Route::get('/requisitions', [RequisitionController::class, 'getRequisitionsForApproval'])->name('supervisor.requisitions.index');
        Route::get('/requisitions/stats', [RequisitionController::class, 'getRequisitionStats'])->name('supervisor.requisitions.stats');
        Route::get('/requisitions/{id}', [RequisitionController::class, 'getRequisitionForReview'])->name('supervisor.requisitions.show');
        Route::post('/requisitions/{id}/status', [RequisitionController::class, 'updateRequisitionStatus'])->name('supervisor.requisitions.status');
    });

    // User Management routes (Admin only)
    Route::middleware(['role:admin'])->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
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

    // Dashboard and specific role routes
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/Admin_dashboard', function () {
            return view('Admin.dashboard');
        })->name('Admin_dashboard');

        Route::get('/Admin_User_Management', [UserController::class, 'index'])->name('Admin_User_Management');
        Route::get('/Admin_Item_Request', function () {
            return view('Admin.Requisition.item_request');
        })->name('Admin_Item_Request');
        Route::get('/Admin_Requisition', function () {
            return view('Admin.Requisition.requisition');
        })->name('Admin_Requisition');
        Route::get('/Admin_Purchase_Order', function () {
            return view('Admin.Purchasing.purchase');
        })->name('Admin_Purchase_Order');
        Route::get('/Admin_Supplier', function () {
            return view('Admin.Purchasing.supplier');
        })->name('Admin_Supplier');
        Route::get('/Admin_Inventory_Transaction', function () {
            return view('Admin.Inventory.inventory_transaction');
        })->name('Admin_Inventory_Transaction');
        Route::get('/Admin_Inventory', function () {
            return view('Admin.Inventory.inventory');
        })->name('Admin_Inventory');
        Route::get('/Admin_Report', function () {
            return view('Admin.report');
        })->name('Admin_Report');
        Route::get('/Admin_Notification', function () {
            return view('Admin.notification');
        })->name('Admin_Notification');
    });

    Route::middleware(['role:employee'])->group(function () {
        Route::get('/Staff_dashboard', function () {
            return view('Employee.dashboard');
        })->name('Staff_dashboard');
        Route::get('/Staff_Create_Requisition', [RequisitionController::class, 'create'])->name('Staff_Create_Requisition');
        Route::get('/Staff_Requisition_Record', function () {
            return view('Employee.Requisition.my_requisition');
        })->name('Staff_Requisition_Record');
        Route::get('/Staff_Item_Request', [ItemRequestController::class, 'create'])->name('Staff_Item_Request');
        Route::get('/Staff_Reciept', function () {
            return view('Employee.acknowledgement_receipt');
        })->name('Staff_Reciept');
        Route::get('/Staff_Notification', function () {
            return view('Employee.notification');
        })->name('Staff_Notification');
    });

    Route::middleware(['role:inventory'])->group(function () {
        Route::get('/Inventory_Dashboard', function () {
            return view('Inventory.dashboard');
        })->name('Inventory_Dashboard');
        Route::get('/Inventory_List', function () {
            return view('Inventory.inventory_list');
        })->name('Inventory_List');
        Route::get('/Inventory_Low_Stock_Alert', function () {
            return view('Inventory.low_stock_alert');
        })->name('Inventory_Low_Stock_Alert_notification');
        Route::get('/Inventory_Notification', function () {
            return view('Inventory.notification');
        })->name('Inventory_Notification');
        Route::get('/Inventory_Report', function () {
            return view('Inventory.report');
        })->name('Inventory_Report');
        Route::get('/Inventory_Stock_in', function () {
            return view('Inventory.stock_in');
        })->name('Inventory_Stock_in');
        Route::get('/Inventory_Stock_out', function () {
            return view('Inventory.stock_out');
        })->name('Inventory_Stock_out');
    });

    Route::middleware(['role:purchasing'])->group(function () {
        Route::get('/Purchasing_dashboard', function () {
            return view('Purchasing.dashboard');
        })->name('Purchasing_dashboard');
        Route::get('/Purchasing_Purchase_Order', function () {
            return view('Purchasing.create_purchase_order');
        })->name('Purchasing_Purchase_Order');
        Route::get('/Purchasing_Approved_Requisition', function () {
            return view('Purchasing.approved_requisition');
        })->name('Purchasing_Approved_Requisition');
        Route::get('/Purchasing_Inventory_overview', function () {
            return view('Purchasing.inventory_overview');
        })->name('Purchasing_Inventory_overview');
        Route::get('/Purchasing_Notification', function () {
            return view('Purchasing.notification');
        })->name('Purchasing_Notification');
        Route::get('/Purchasing_Report', function () {
            return view('Purchasing.report');
        })->name('Purchasing_Report');
        Route::get('/Purchasing_Supplier', function () {
            return view('Purchasing.supplier');
        })->name('Purchasing_Supplier');
    });

    Route::middleware(['role:supervisor'])->group(function () {
        Route::get('/Supervisor_Dashboard', function () {
            return view('Supervisor.dashboard');
        })->name('Supervisor_Dashboard');
        Route::get('/Supervisor_Requisition', function () {
            return view('Supervisor.Requisition.requisition');
        })->name('Supervisor_Requisition');
        Route::get('/Supervisor_Item_Request', function () {
            return view('Supervisor.Requisition.item_request');
        })->name('Supervisor_Item_Request');
        Route::get('/Supervisor_Purchase_Order', function () {
            return view('Supervisor.purchase_order');
        })->name('Supervisor_Purchase_Order');
        Route::get('/Supervisor_Inventory_Overview', function () {
            return view('Supervisor.inventory_overview');
        })->name('Supervisor_Inventory_Overview');
        Route::get('/Supervisor_Report', function () {
            return view('Supervisor.report');
        })->name('Supervisor_Report');
        Route::get('/Supervisor_Notification', function () {
            return view('Supervisor.notification');
        })->name('Supervisor_Notification');
    });
});

// API Routes for AJAX calls
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Item API routes
    Route::get('/items/requisition', [ItemController::class, 'getItemsForRequisition']);
    Route::get('/items/{id}', [ItemController::class, 'getItemDetails']);
    Route::get('/items/category/{categoryId}', [ItemController::class, 'getItemsByCategory']);
    Route::get('/items/search', [ItemController::class, 'searchItems']);

    // Requisition API routes
    Route::post('/requisitions', [RequisitionController::class, 'store']);
    Route::get('/requisitions/my', [RequisitionController::class, 'getMyRequisitions']);
    Route::get('/requisitions/all', [RequisitionController::class, 'getAllRequisitions']);
    Route::get('/requisitions/{id}', [RequisitionController::class, 'getRequisitionDetails']);
    Route::post('/requisitions/{id}/status', [RequisitionController::class, 'updateStatus']);
    Route::delete('/requisitions/{id}', [RequisitionController::class, 'destroy']);

    // Item Request API routes
    Route::post('/item-requests', [ItemRequestController::class, 'store']);
    Route::get('/item-requests/my', [ItemRequestController::class, 'getMyRequests']);
    Route::get('/item-requests/{id}', [ItemRequestController::class, 'show']);
    Route::get('/item-requests/pending', [ItemRequestController::class, 'getPendingRequests']);
    Route::post('/item-requests/{id}/approve', [ItemRequestController::class, 'approve']);
    Route::post('/item-requests/{id}/reject', [ItemRequestController::class, 'reject']);

    // Inventory API routes
    Route::get('/inventory/items', [RequisitionController::class, 'getInventoryItems']);
});
