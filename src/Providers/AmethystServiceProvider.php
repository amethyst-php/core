<?php

namespace Amethyst\Core\Providers;

use Amethyst\Core\Map;
use Illuminate\Support\ServiceProvider;
use Railken\EloquentMapper\Contracts\Map as MapContract;

class AmethystServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->register(\Railken\Lem\Providers\ManagerServiceProvider::class);
        $this->app->register(\Amethyst\Core\Providers\ApiServiceProvider::class);
        $this->app->register(\Railken\EloquentMapper\EloquentMapperServiceProvider::class);
        $this->app->bind(MapContract::class, Map::class);
        $this->app->singleton('amethyst', function ($app) {
            return new \Amethyst\Core\Helper();
        });
    }

    public function boot()
    {
        app('amethyst')->boot();
        
        $this->loadRoutesFrom(__DIR__.'/../../resources/routes.php');
    }
}
