<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
    }
}
