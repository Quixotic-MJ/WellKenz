<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// Public routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===========================================
// Admin Routes (sidebar pages)
// ===========================================
Route::get('/Admin_dashboard', function () {
    return view('Admin.dashboard');
})->name('Admin_dashboard');

Route::get('/Admin_Employee_Management', function () {
    return view('Admin.Management.employee_management');
})->name('Admin_Employee_Management');

Route::get('/Admin_User_Management', function () {
    return view('Admin.Management.user_management');
})->name('Admin_User_Management');

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


// ===========================================
// Employee Routes (sidebar pages)
// ===========================================
Route::get('/Staff_dashboard', function () {
    return view('Employee.dashboard');
})->name('Staff_dashboard');

Route::get('/Staff_Create_Requisition', function () {
    return view('Employee.Requisition.create_requisition');
})->name('Staff_Create_Requisition');

Route::get('/Staff_Requisition_Record', function () {
    return view('Employee.Requisition.my_requisition');
})->name('Staff_Requisition_Record');

Route::get('/Staff_Item_Request', function () {
    return view('Employee.item_request');
})->name('Staff_Item_Request');

Route::get('/Staff_Reciept', function () {
    return view('Employee.acknowledgement_receipt');
})->name('Staff_Reciept');

Route::get('/Staff_Notification', function () {
    return view('Employee.notification');
})->name('Staff_Notification');


// ===========================================
// Inventory Staff Routes (sidebar pages)
// ===========================================
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


// ===========================================
// Purchasing Routes (sidebar pages)
// ===========================================
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


// ===========================================
// Supervisor Routes (sidebar pages)
// ===========================================
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
