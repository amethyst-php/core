<?php

namespace Amethyst\Core\Tests\App\Schemas;

use Amethyst\Core\Tests\App\Managers\BarManager;
use Railken\Lem\Attributes;
use Railken\Lem\Schema;

class FooSchema extends Schema
{
    /**
     * Get all attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return [
            Attributes\IdAttribute::make(),
            Attributes\TextAttribute::make('name'),
            Attributes\TextAttribute::make('description')->setMaxLength(4096),
            Attributes\BelongsToAttribute::make('bar_id')
                ->setRelationName('bar')
                ->setRelationManager(BarManager::class),
            Attributes\CreatedAtAttribute::make(),
            Attributes\UpdatedAtAttribute::make(),
        ];
    }
}
