<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$routeName): Response
    {
        if(!auth()->check()) return abort(401);
        $user = auth()->user();
        $currentRouteName = $request->route()->getName();
        if(empty($currentRouteName)) return abort(401);
        if($user->hasPermissionToAccessRoute($currentRouteName)) return $next($request);

        return abort(403);
    }
}
