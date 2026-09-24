<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrapFive();

        // @peut('cons.stock') ... @endpeut : masque un élément si le rôle n'a pas le droit
        Blade::if('peut', fn (string $regle) => \App\Support\Droits::autorise($regle));

        // Custom user provider that validates plain-text passwords
        // since the velaro database stores passwords as plain text
        Auth::provider('velaro', function ($app, array $config) {
            return new \App\Auth\VelaroUserProvider($app['hash'], $config['model']);
        });
    }
}
