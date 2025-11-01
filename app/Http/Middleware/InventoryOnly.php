<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InventoryOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (session('role') !== 'inventory') {
            return redirect()->back()->with('error', 'Access denied. Inventory staff privileges required.');
        }

        return $next($request);
    }
}