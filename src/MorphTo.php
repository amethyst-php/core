<?php

namespace Amethyst\Core;

use Illuminate\Database\Eloquent\Relations\MorphTo as BaseMorphTo;
use Railken\EloquentMapper\Contracts\Map as MapContract;

class MorphTo extends BaseMorphTo
{
    /**
     * Create a new model instance by type.
     *
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModelByType($type)
    {
        return app(MapContract::class)->keyToModel($type);
    }
}