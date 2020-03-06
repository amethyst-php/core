<?php

namespace Amethyst\Core;

use Illuminate\Database\Eloquent\Model;
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
            $data->boot();

            return $data->newEntity();
        })->all();
    }

    /**
     * Convert a model to a unique key.
     *
     * @param Model $model
     *
     * @return string
     */
    public function modelToKey(Model $model): string
    {
        return $model->getMorphClass();
    }

    /**
     * Convert key to a new instance of a model.
     *
     * @param string $key
     *
     * @return Model
     */
    public function keyToModel(string $key): Model
    {
        return app('amethyst')->findManagerByName($key)->newEntity();
    }
}
