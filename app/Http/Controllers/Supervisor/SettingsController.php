<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function branchSetting(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->branchSetting($request);
    }

    public function updateMinimumStockLevel(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->updateMinimumStockLevel($request);
    }

    public function applySeasonalAdjustment(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->applySeasonalAdjustment($request);
    }

    public function getStockConfigurationData(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->getStockConfigurationData($request);
    }
}
