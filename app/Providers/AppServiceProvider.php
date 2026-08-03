<?php

namespace App\Providers;

use App\Services\Core\CoreSsoProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Gate::before(function ($user, string $ability) {
            return $user->hasRole('Superadmin') ? true : null;
        });

        if (class_exists(Socialite::class)) {
            Socialite::extend('core', function ($app) {
                $config = $app['config']['services.core'] ?? [];

                return Socialite::buildProvider(CoreSsoProvider::class, $config);
            });
        }
    }
}
