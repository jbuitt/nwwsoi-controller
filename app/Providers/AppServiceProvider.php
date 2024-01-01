<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            if (config('app.force_https_urls') !== false) {
                URL::forceScheme('https');
            }
        } elseif (config('app.env') === 'local') {
            if (config('app.force_https_urls')) {
                URL::forceScheme('https');
            }
        }
        Gate::define('viewPulse', function (User $user) {
            return $user->id === 1;
        });
    }
}
