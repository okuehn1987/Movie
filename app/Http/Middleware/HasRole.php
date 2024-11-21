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
    public static function handle($request, Closure $next, string ...$roles)
    {
        if (Auth::check() && in_array(Auth::user()->role, $roles))
            return $next($request);
        return redirect(route('home'));
    }
}
