<?php

namespace App\Providers;

use App\Models\Organization;
use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!$this->app->environment('local'))
            \Illuminate\Support\Facades\URL::forceScheme('https');
        Model::shouldBeStrict($this->app->environment('local'));

        DB::connection()->enableQueryLog();
        DB::listen(function (QueryExecuted $event) {
            if ($event->time > 50) {
                Log::warning('Slow query', ['sql' => Str::replaceArray('?', $event->bindings, $event->sql), 'time' => $event->time]);
            }

            if (env('QUERY_LOG') && !str_contains($event->sql, 'querylogs')) {
                $bindings = $event->bindings;
                $sql = $event->sql;
                dispatch(function () use ($sql, $bindings) {
                    try {
                        DB::table('querylogs')->insert([
                            'user_id' => Auth::id(),
                            'query' => Str::replaceArray('?', $bindings, $sql),
                            'executed_at' => now(),
                        ]);
                    } catch (Exception $e) {
                        Log::warning($e);
                    }
                })->afterResponse();
            }
        });

        Vite::prefetch(concurrency: 3);

        Gate::defaultDenialResponse(app()->environment('local') ? Response::denyWithStatus(403) : Response::denyAsNotFound());

        Feature::resolveScopeUsing(fn() => Organization::getCurrent());

        EnsureFeaturesAreActive::whenInactive(fn() => new Response(428));

        foreach (Organization::$FLAGS as $feature => $description) {
            Feature::define($feature, fn(Organization $organization) => !!$organization->{$feature});
        }
    }
}
