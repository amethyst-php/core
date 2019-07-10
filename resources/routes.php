<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Amethyst\Api\Support\Router;

foreach (Config::get('amethyst') as $packageName => $package) {
    foreach ((array)Config::get('amethyst.'.$packageName.'.http') as $groupName => $group) {
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