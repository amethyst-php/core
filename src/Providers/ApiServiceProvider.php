<?php

namespace Amethyst\Core\Providers;

use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/amethyst.api.php' => config_path('amethyst.api.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->register(\Railken\Lem\Providers\ManagerServiceProvider::class);
        $this->app->register(\Railken\EloquentMapper\EloquentMapperServiceProvider::class);
        $this->mergeConfigFrom(__DIR__.'/../../config/amethyst.api.php', 'amethyst.api');
    }
}
