<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EmployeeOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (session('role') !== 'employee') {
            return redirect()->back()->with('error', 'Access denied. Employee privileges required.');
        }

        return $next($request);
    }
}