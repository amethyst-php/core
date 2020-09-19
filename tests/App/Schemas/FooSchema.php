<?php

namespace Amethyst\Core\Tests\App\Schemas;

use Amethyst\Core\Tests\App\Managers\BarManager;
use Railken\Lem\Attributes;
use Railken\Lem\Schema;

class FooSchema extends Schema
{
    /**
     * Get all the attributes.
     *
     * @var array
     */
    public function getAttributes(): array
    {
        return [
            Attributes\IdAttribute::make(),
            Attributes\TextAttribute::make('name')
                ->setRequired(true),
            Attributes\LongTextAttribute::make('description'),
            Attributes\BelongsToAttribute::make('bar_id')
                ->setRelationName('bar')
                ->setRelationManager(BarManager::class),
            Attributes\CreatedAtAttribute::make(),
            Attributes\UpdatedAtAttribute::make(),
            Attributes\DeletedAtAttribute::make(),
        ];
    }
}
