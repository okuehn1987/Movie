<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
use Symfony\Component\HttpFoundation\Response;

class CheckIfGateWasUsedToAuthorizeRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (!collect(headers_list())->contains(fn($h) => str_contains($h, 'authorized-by-gate')))
            if (app()->environment('local')) {
                throw new class("No gate was used to authorize this request: " . request()->method() . " route('" . request()->route()->action['as'] . "')") extends Exception implements ProvidesSolution
                {
                    public function getSolution(): Solution
                    {
                        return BaseSolution::create()
                            ->setSolutionDescription(
                                "Check the controller method\n\n`" . request()->route()->action["controller"] . "`\n\n" .
                                    "Make sure to use the gate facade to authorize the request:\n\n`Gate::authorize('...', ...)`.\n\n" .
                                    "Also make sure the gate used does set the header in its before method:\n\n `header('authorized-by-gate: ' . self::class)`."
                            );
                    }
                };
            } else {
                throw new AuthorizationException('No Gate was used to authorize request');
            }

        header_remove('authorized-by-gate');

        return $response;
    }
}
