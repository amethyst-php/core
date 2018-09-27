<?php

namespace Railken\Amethyst\Common;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Railken\Amethyst\Api\Support\Router;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Config file.
     *
     * @var string
     */
    protected $config;

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->publishes([__DIR__.'/../config/'.$this->config.'.php' => config_path(''.$this->config.'.php')], 'config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutes();
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->register(\Railken\Lem\Providers\ManagerServiceProvider::class);
        $this->app->register(\Railken\Amethyst\ApiServiceProvider::class);
        $this->mergeConfigFrom(__DIR__.'/../config/'.$this->config.'.php', ''.$this->config.'');
    }

    /**
     * Load routes.
     */
    public function loadRoutes()
    {
        $config = Config::get($this->config.'.http.admin');

        if (Arr::get($config, 'enabled')) {
            Router::group('admin', Arr::get($config, 'router'), function ($router) use ($config) {
                $controller = Arr::get($config, 'controller');

                $router->get('/', ['uses' => $controller.'@index']);
                $router->post('/', ['uses' => $controller.'@create']);
                $router->put('/{id}', ['uses' => $controller.'@update']);
                $router->delete('/{id}', ['uses' => $controller.'@remove']);
                $router->get('/{id}', ['uses' => $controller.'@show']);
            });
        }
    }
}
