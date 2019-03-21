<?php

namespace Railken\Amethyst\Common;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Collection;
use Doctrine\Common\Inflector\Inflector;

class Helper
{
    public function getData()
    {
        $return = Collection::make();

        foreach (array_keys(Config::get('amethyst')) as $config) {
            foreach (Config::get('amethyst.'.$config.'.data', []) as $nameData => $data) {
                if (Arr::get($data, 'model')) {
                    $return[] = $data;
                }
            }
        }

        return $return;
    }

    public function findDataByModel(string $class)
    {   
        return $this->getData()->filter(function ($data) use ($class) {

            return Arr::get($data, 'model') === $class;
        })->first();
    }

    public function getNameDataByModel(string $class)
    {
        return str_replace('_', '-', (new Inflector())->tableize((new \ReflectionClass($class))->getShortName()));
    }
}
