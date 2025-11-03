<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PurchasingOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->role !== 'purchasing') {
            return redirect()->route('login')->with('error', 'Access denied. Purchasing staff privileges required.');
        }

        return $next($request);
    }
}