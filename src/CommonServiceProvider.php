<?php

namespace Railken\Amethyst\Common;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Railken\Amethyst\Api\Support\Router;

class CommonServiceProvider extends ServiceProvider
{
    /**
     * Array of config files.
     *
     * @var array
     */
    protected $configFiles = [];

    /**
     * Get current directory.
     *
     * @return string
     */
    public function getDirectory()
    {
        $reflector = new \ReflectionClass($this);

        return dirname($reflector->getFileName());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom($this->getDirectory().'/../../database/migrations');
        $this->loadRoutes();
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->loadConfigs();
        $this->app->register(\Railken\Lem\Providers\ManagerServiceProvider::class);
        $this->app->register(\Railken\Amethyst\Providers\ApiServiceProvider::class);
    }

    /**
     * Load configs-.
     */
    public function loadConfigs()
    {
        $directory = $this->getDirectory().'/../../config/*';

        foreach (glob($directory) as $file) {
            $this->publishes([$file => config_path(basename($file))], 'config');
            $this->mergeConfigFrom($file, basename($file, '.php'));
        }
    }

    /**
     * Load routes based on configs.
     */
    public function loadRoutes()
    {
        $inflector = new Inflector();

        $reflection = new \ReflectionClass($this);
        $packageName = str_replace('_', '-', $inflector->tableize(str_replace('ServiceProvider', '', $reflection->getShortName())));

        foreach (Config::get('amethyst.'.$packageName.'.http') as $groupName => $group) {
            foreach ($group as $configName => $config) {
                if (Arr::get($config, 'enabled')) {
                    Router::group($groupName, Arr::get($config, 'router'), function ($router) use ($config) {
                        $controller = Arr::get($config, 'controller');

                        $reflection = new \ReflectionClass($controller);

                        if ($reflection->hasMethod('index')) {
                            $router->get('/', ['as' => 'index', 'uses' => $controller.'@index']);
                        }

                        if ($reflection->hasMethod('store')) {
                            $router->put('/', ['as' => 'store', 'uses' => $controller.'@store']);
                        }

                        if ($reflection->hasMethod('erase')) {
                            $router->delete('/', ['as' => 'erase', 'uses' => $controller.'@erase']);
                        }

                        if ($reflection->hasMethod('create')) {
                            $router->post('/', ['as' => 'create', 'uses' => $controller.'@create']);
                        }

                        if ($reflection->hasMethod('update')) {
                            $router->put('/{id}', ['as' => 'update', 'uses' => $controller.'@update']);
                        }

                        if ($reflection->hasMethod('remove')) {
                            $router->delete('/{id}', ['as' => 'remove', 'uses' => $controller.'@remove']);
                        }

                        if ($reflection->hasMethod('show')) {
                            $router->get('/{id}', ['as' => 'show', 'uses' => $controller.'@show']);
                        }
                    });
                }
            }
        }
    }
}
