<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\Response;

class AllowMobileAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Feature::inactive('allow_mobile_access')) {
            $mobileRegEx = '/Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/';
            $userAgent = ($request->header('user-agent'));

            if (preg_match($mobileRegEx, $userAgent) === 1) {
                abort(403, 'Der mobile Zugriff auf diese Website ist von Ihrer Organisation untersagt worden.');
            }
        };

        return $next($request);
    }
}
