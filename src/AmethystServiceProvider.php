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
        $this->app->singleton('amethyst', function ($app) {
            return new \Railken\Amethyst\Common\Helper();
        });
    }
}
