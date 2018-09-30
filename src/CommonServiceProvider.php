<?php

namespace Railken\Amethyst\Common;

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
            $this->publishes([basename($file) => config_path(basename($file))], 'config');
            $this->mergeConfigFrom($file, basename($file, '.php'));
            $this->configFiles[] = $file;
        }
    }

    /**
     * Load routes based on configs.
     */
    public function loadRoutes()
    {
        foreach ($this->configFiles as $file) {
            $index = basename($file, '.php');
            foreach (Config::get($index.'.http') as $groupName => $group) {
                foreach ($group as $configName => $config) {
                    if (Arr::get($config, 'enabled')) {
                        Router::group($groupName, Arr::get($config, 'router'), function ($router) use ($config) {
                            $controller = Arr::get($config, 'controller');

                            $reflection = new \ReflectionClass($controller);

                            if ($reflection->hasMethod('index')) {
                                $router->get('/', ['uses' => $controller.'@index']);
                            }

                            if ($reflection->hasMethod('create')) {
                                $router->post('/', ['uses' => $controller.'@create']);
                            }

                            if ($reflection->hasMethod('update')) {
                                $router->put('/{id}', ['uses' => $controller.'@update']);
                            }

                            if ($reflection->hasMethod('remove')) {
                                $router->delete('/{id}', ['uses' => $controller.'@remove']);
                            }

                            if ($reflection->hasMethod('show')) {
                                $router->get('/{id}', ['uses' => $controller.'@show']);
                            }
                        });
                    }
                }
            }
        }
    }
}
