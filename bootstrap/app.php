<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register our custom middleware
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'check.auth' => \App\Http\Middleware\CheckAuth::class,
            'admin.only' => \App\Http\Middleware\AdminOnly::class,
            'employee.only' => \App\Http\Middleware\EmployeeOnly::class,
            'inventory.only' => \App\Http\Middleware\InventoryOnly::class,
            'purchasing.only' => \App\Http\Middleware\PurchasingOnly::class,
            'supervisor.only' => \App\Http\Middleware\SupervisorOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();