<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public static function handle(Request $request, Closure $next,  ...$permissions)
    {
        if (!Auth::check()) return redirect(route('home'));

        foreach ($permissions as $permission) {
            if (!Auth::user()->{$permission}) {
                return redirect(route('home'));
            }
        }
        return $next($request);
    }
}
