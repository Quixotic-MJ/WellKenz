<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Auth.login');
});

// ===========================================
// Admin
// ===========================================

Route::get('/Admin_dashboard', function () {
    return view('Admin.dashboard');
})->name('Admin_dashboard');

Route::get('/Admin_requisition', function () {
    return view('Admin.requisition');
})->name('Admin_requisition');

Route::get('/Admin_purchasing', function () {
    return view('Admin.purchasing');
})->name('Admin_purchasing');

Route::get('/Admin_inventory', function () {
    return view('Admin.inventory');
})->name('Admin_inventory');

Route::get('/Admin_report', function () {
    return view('Admin.report');
})->name('Admin_report');

Route::get('/Admin_user', function () {
    return view('Admin.user');
})->name('Admin_user');

Route::get('/Admin_notification', function () {
    return view('Admin.notification');
})->name('Admin_notification');

// ===========================================
// Employee
// ===========================================

Route::get('/Staff_dashboard', function () {
    return view('Employee.dashboard');
})->name('Staff_dashboard');

Route::get('/Staff_requisition', function () {
    return view('Employee.requisition');
})->name('Staff_requisition');

Route::get('/Staff_history', function () {
    return view('Employee.history');
})->name('Staff_history');

Route::get('/Staff_notification', function () {
    return view('Employee.Notification');
})->name('Staff_notification');

// ===========================================
// Inventory Staff
// ===========================================

Route::get('/Inventory_dashboard', function () {
    return view('Inventory.dashboard');
})->name('Inventory_dashboard');

Route::get('/Inventory_info', function () {
    return view('Inventory.Inventory');
})->name('Inventory_info');

Route::get('/Inventory_notification', function () {
    return view('Inventory.notification');
})->name('Inventory_notification');

Route::get('/Inventory_report', function () {
    return view('Inventory.report');
})->name('Inventory_report');

Route::get('/Inventory_StockIn', function () {
    return view('Inventory.Stock_in');
})->name('Inventory_StockIn');

Route::get('/Inventory_StockOut', function () {
    return view('Inventory.Stock_out');
})->name('Inventory_StockOut');

Route::get('/Inventory_transaction', function () {
    return view('Inventory.transaction');
})->name('Inventory_transaction');


// ===========================================
// Purchasing
// ===========================================

Route::get('/Purchasing_dashboard', function () {
    return view('Purchasing.dashboard');
})->name('Purchasing_dashboard');

Route::get('/Purchasing_requisition', function () {
    return view('Purchasing.requisition');
})->name('approvedRequisition');

Route::get('/Purchasing_purchase', function () {
    return view('Purchasing.purchase');
})->name('purchaseOrder');

Route::get('/Purchasing_suppplier', function () {
    return view('Purchasing.supplier');
})->name('Purchasing_supplier');

Route::get('/Purchasing_memo', function () {
    return view('Purchasing.memo');
})->name('receivingMemos');

Route::get('/Purchasing_inventory', function () {
    return view('Purchasing.inventory');
})->name('Purchasing_inventory');

Route::get('/Purchasing_report', function () {
    return view('Purchasing.report');
})->name('Purchasing_report');

Route::get('/Purchasing_notification', function () {
    return view('Purchasing.notification');
})->name('Purchasing_notification');


// ===========================================
// Supervisor
// ===========================================

Route::get('/Supervisor_dashboard', function () {
    return view('Supervisor.dashboard');
})->name('Purchasing_dashboard');

Route::get('/Supervisor_requisition', function () {
    return view('Purchasing.requisition');
})->name('approvedRequisition');

Route::get('/Supervisor_purchase', function () {
    return view('Purchasing.purchase');
})->name('purchaseOrder');

Route::get('/Supervisor_suppplier', function () {
    return view('Purchasing.supplier');
})->name('Purchasing_supplier');

Route::get('/Supervisor_memo', function () {
    return view('Purchasing.memo');
})->name('receivingMemos');

Route::get('/Supervisor_inventory', function () {
    return view('Purchasing.inventory');
})->name('Purchasing_inventory');

Route::get('/Supervisor_report', function () {
    return view('Purchasing.report');
})->name('Purchasing_report');

Route::get('/Supervisor_notification', function () {
    return view('Purchasing.notification');
})->name('Purchasing_notification');
