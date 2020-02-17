<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Amethyst\Core\Support\Router;

/*
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
}*/

foreach (Config::get('amethyst.api.http') as $name => $config) {
    if ($name === 'data') {
        Router::group($name, ['prefix' => '{name}'], function ($router) use ($name, $config) {

            $controller = \Amethyst\Core\Http\Controllers\BasicController::class;

            $router->get('/', ['as' => 'index', 'uses' => $controller.'@index']);
            $router->put('/', ['as' => 'store', 'uses' => $controller.'@store']);
            $router->delete('/', ['as' => 'erase', 'uses' => $controller.'@erase']);
            $router->post('/', ['as' => 'create', 'uses' => $controller.'@create']);
            $router->put('/{id}', ['as' => 'update', 'uses' => $controller.'@update']);
            $router->delete('/{id}', ['as' => 'remove', 'uses' => $controller.'@remove']);
            $router->get('/{id}', ['as' => 'show', 'uses' => $controller.'@show']);
        });
    }
}
