<?php

namespace Amethyst\Core;

use Illuminate\Support\Facades\Config;

trait ConfigurableManager
{
    /**
     * Initialize the model by the configuration.
     */
    public function bootConfig()
    {
        $this->comment = Config::get($this->config.'.comment');
    }

    /**
     * Register Classes.
     */
    public function registerClasses()
    {
        return Config::get($this->config);
    }
}
