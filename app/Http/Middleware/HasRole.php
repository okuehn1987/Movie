<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public static function handle($request, Closure $next, string $role)
    {
        if (Auth::check() && Auth::user()->role == $role)
            return $next($request);
        return redirect(route('home'));
    }
}
