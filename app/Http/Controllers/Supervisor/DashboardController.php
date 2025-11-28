<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function home()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->home();
    }

    public function getStockOverview()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->getStockOverview();
    }

    public function getProductionMetrics()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->getProductionMetrics();
    }
}
