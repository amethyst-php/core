<?php

namespace Railken\Amethyst\Common;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;

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
        $this->app->register(\Railken\EloquentMapper\EloquentMapperServiceProvider::class);

        $this->app->singleton('amethyst', function ($app) {
            return new \Railken\Amethyst\Common\Helper();
        });

        $this->app->get('eloquent.mapper')->retriever(function () {
            return $this->app->get('amethyst')->getData()->map(function ($data) {
                return Arr::get($data, 'model');
            })->toArray();
        });
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__."/../resources/routes.php");
    }
}
