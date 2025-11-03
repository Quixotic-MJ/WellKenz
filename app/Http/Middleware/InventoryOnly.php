<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class InventoryOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== 'inventory') {
            return redirect()->route('login')->with('error', 'Access denied. Inventory staff privileges required.');
        }

        return $next($request);
    }
}