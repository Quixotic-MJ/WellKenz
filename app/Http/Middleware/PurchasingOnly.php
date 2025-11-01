<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PurchasingOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (session('role') !== 'purchasing') {
            return redirect()->back()->with('error', 'Access denied. Purchasing staff privileges required.');
        }

        return $next($request);
    }
}