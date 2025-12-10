<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsaActive
{
    public function handle(Request $request, Closure $next)
    {
        $organization = Auth::user()->organization ?? null;

        if (!$organization || !$organization->isa_active) {
            abort(403, 'ISA is not active.');
        }

        return $next($request);
    }
}
