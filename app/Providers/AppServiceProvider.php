<?php

namespace App\Providers;

use App\Models\Organization;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

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

        Vite::prefetch(concurrency: 3);

        Gate::defaultDenialResponse(app()->environment('local') ? Response::denyWithStatus(403) : Response::denyAsNotFound());

        Feature::resolveScopeUsing(fn() => Organization::getCurrent());

        EnsureFeaturesAreActive::whenInactive(fn() => new Response(428));

        foreach (Organization::$FLAGS as $feature => $description) {
            Feature::define($feature, fn(Organization $organization) => !!$organization->{$feature});
        }
    }
}
