<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function expiryReport(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->expiryReport($request);
    }

    public function printUseFirstList(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->printUseFirstList($request);
    }

    public function alertBakers(Request $request)
    {
        return app(\App\Http\Controllers\SupervisorController::class)->alertBakers($request);
    }
}
