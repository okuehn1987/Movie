<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Services\AppModuleService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isApp
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param string $app as 'herta' | 'timesheets'
     */
    public function handle(Request $request, Closure $next, string $app): Response
    {
        if (AppModuleService::currentAppModule() !== $app) {
            if (AppModuleService::hasAppModule($app)) {
                AppModuleService::setCurrentAppModule($app);
                return redirect($request->fullUrl());
            }
            abort(402);
        }
        return $next($request);
    }
}
