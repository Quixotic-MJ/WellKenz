<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Auth.login');
});

Route::get('/dashboard', function () {
    return view('Admin.dashboard');
})->name('dashboard');

Route::get('/requisition', function () {
    return view('Admin.requisition');
})->name('requisition');

Route::get('/purchasing', function () {
    return view('Admin.purchasing');
})->name('purchasing');

Route::get('/inventory', function () {
    return view('Admin.inventory');
})->name('inventory');

Route::get('/report', function () {
    return view('Admin.report');
})->name('report');

Route::get('/user', function () {
    return view('Admin.user');
})->name('user');

Route::get('/notification', function () {
    return view('Admin.notification');
})->name('notification');