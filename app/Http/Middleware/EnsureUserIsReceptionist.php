<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsReceptionist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->role !== 'receptionist') {
            if ($request->user()->role === 'manager') {
                return redirect()->route('filament.manager.pages.dashboard');
            } elseif ($request->user()->role === 'housekeeping') {
                return redirect()->route('filament.housekeeping.pages.dashboard');
            }

            Auth::logout();
        }

        return $next($request);
    }
}
