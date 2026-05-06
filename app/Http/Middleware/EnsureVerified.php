<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is authenticated but not verified, redirect with error
        if (Auth::check() && !Auth::user()->is_verified) {
            return redirect('/jobs')->with('error', 'Please verify your account to continue.');
        }

        return $next($request);
    }
}
