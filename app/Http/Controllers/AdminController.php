<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function dashboard()
    {
        return view('Admin.dashboard');
    }

    public function requisition()
    {
        return view('Admin.requisition');
    }

    public function purchasing()
    {
        return view('Admin.purchasing');
    }

    public function inventory()
    {
        return view('Admin.inventory');
    }

    public function report()
    {
        return view('Admin.report');
    }

    public function user()
    {
        return view('Admin.user');
    }

    public function notification()
    {
        return view('Admin.notification');
    }
}