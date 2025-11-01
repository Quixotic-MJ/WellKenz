<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AdminController;

// Public routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes - require authentication
Route::middleware(['check.auth'])->group(function () {

    // ===========================================
    // Admin Routes
    // ===========================================
    Route::middleware(['admin.only'])->group(function () {
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

        Route::get('/Admin_user', [UserController::class, 'index'])->name('Admin_user');
        Route::get('/Admin_employee', [EmployeeController::class, 'index'])->name('Admin_employee');

        Route::get('/Admin_notification', function () {
            return view('Admin.notification');
        })->name('Admin_notification');

        // Department Management CRUD Routes
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{id}', [DepartmentController::class, 'show'])->name('departments.show');
        Route::get('/departments/{id}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{id}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{id}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        // Employee Management CRUD Routes
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

        // User Management CRUD Routes
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ===========================================
    // Employee Routes
    // ===========================================
    Route::middleware(['employee.only'])->group(function () {
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
    });

    // ===========================================
    // Inventory Staff Routes
    // ===========================================
    Route::middleware(['inventory.only'])->group(function () {
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
    });

    // ===========================================
    // Purchasing Routes
    // ===========================================
    Route::middleware(['purchasing.only'])->group(function () {
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
    });

    // ===========================================
    // Supervisor Routes
    // ===========================================
    Route::middleware(['supervisor.only'])->group(function () {
        Route::get('/Supervisor_dashboard', function () {
            return view('Supervisor.dashboard');
        })->name('Supervisor_dashboard');

        Route::get('/Supervisor_requisition', function () {
            return view('Supervisor.requisition');
        })->name('Supervisor_requisition');

        Route::get('/Supervisor_purchase', function () {
            return view('Supervisor.purchase');
        })->name('Supervisor_purchase');

        Route::get('/Supervisor_suppplier', function () {
            return view('Supervisor.supplier');
        })->name('Supervisor_supplier');

        Route::get('/Supervisor_memo', function () {
            return view('Supervisor.memo');
        })->name('Supervisor_memo');

        Route::get('/Supervisor_inventory', function () {
            return view('Supervisor.inventory');
        })->name('Supervisor_inventory');

        Route::get('/Supervisor_report', function () {
            return view('Supervisor.report');
        })->name('Supervisor_report');

        Route::get('/Supervisor_notification', function () {
            return view('Supervisor.notification');
        })->name('Supervisor_notification');
    });
});