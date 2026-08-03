<?php

namespace App\Providers;

use App\Services\Core\CoreSsoProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Contracts\Factory;

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

        $socialite = $this->app->make(Factory::class);
        $socialite->extend('core', function ($app) use ($socialite) {
            $config = $app['config']['services.core'];

            return $socialite->buildProvider(CoreSsoProvider::class, $config);
        });
    }
}
