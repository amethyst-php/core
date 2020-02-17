<?php

namespace Amethyst\Core;

use Railken\EloquentMapper\Map as BaseMap;

class Map extends BaseMap
{
    /**
     * Return an array of all models you want to map.
     *
     * @return array
     */
    public function models(): array
    {
        return app('amethyst')->getData()->map(function ($data) {
            return $data->newEntity();
        })->all();
    }
}
