<?php

namespace Railken\Amethyst\Common;

use Illuminate\Support\ServiceProvider;

class AmethystServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->register(\Railken\Lem\Providers\ManagerServiceProvider::class);
        $this->app->register(\Railken\Amethyst\Providers\ApiServiceProvider::class);
        $this->app->register(\Railken\Amethyst\Documentation\GeneratorServiceProvider::class);
        $this->app->singleton('amethyst', function ($app) {
            return new \Railken\Amethyst\Common\Helper();
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__."/../resources/routes.php");
    }
}
