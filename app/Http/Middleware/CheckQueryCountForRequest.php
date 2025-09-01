<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckQueryCountForRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $queries = DB::getQueryLog();

        if (count($queries) > 100) {
            Log::warning('High query count detected', [
                'auth_id' => Auth::id(),
                'route' => $request->route()->getName(),
                'method' => $request->method(),
                'query_count' => count($queries),
            ]);
        }

        return $response;
    }
}
