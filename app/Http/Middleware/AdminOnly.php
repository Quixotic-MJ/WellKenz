<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (session('role') !== 'admin') {
            return redirect()->back()->with('error', 'Access denied. Administrator privileges required.');
        }

        return $next($request);
    }
}