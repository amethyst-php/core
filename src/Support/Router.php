<?php

namespace Amethyst\Core\Support;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class Router
{
    public static function group(string $container, array $config, \Closure $closure)
    {
        return Route::group(Config::get('amethyst.api.http.'.$container.'.router', []), function ($router) use ($config, $closure) {
            return Route::group($config, $closure);
        });
    }
}
