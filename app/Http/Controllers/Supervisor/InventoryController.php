<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Views
    public function stockLevel(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->stockLevel($request);
    }

    public function stockHistory()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->stockHistory();
    }

    public function stockCard(Request $request, Item $item)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->stockCard($request, $item);
    }

    public function inventoryAdjustments()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->inventoryAdjustments();
    }

    public function printStockReport(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->printStockReport($request);
    }

    public function exportStockCSV()
    {
        return app(\App\Http\Controllers\SupervisorController::class)->exportStockCSV();
    }

    // APIs
    public function getItemDetails(Item $item)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->getItemDetails($item);
    }

    public function createAdjustment(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->createAdjustment($request);
    }

    public function getAdjustmentHistory(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->getAdjustmentHistory($request);
    }
}
