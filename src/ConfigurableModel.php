<?php

namespace Railken\Amethyst\Common;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Railken\Lem\Attributes;

trait ConfigurableModel
{
    /**
     * Initialize the model by the configuration.
     *
     * @param string $config
     */
    public function ini($config)
    {
        $this->table = Config::get($config.'.table', 'barabba');

        $classSchema = Config::get($config.'.schema');
        $schema = new $classSchema();

        $attributes = collect(($schema)->getAttributes());

        $this->iniFillable($attributes);
        $this->iniDates($attributes);
        $this->iniCasts($attributes);
    }

    /**
     * Initialize fillable by attributes.
     *
     * @param Collection $attributes
     */
    public function iniFillable(Collection $attributes)
    {
        $this->fillable = $attributes->filter(function ($attribute) {
            return $attribute->getFillable();
        })->map(function ($attribute) {
            return $attribute->getName();
        })->toArray();
    }

    /**
     * Initialize dates by attributes.
     *
     * @param Collection $attributes
     */
    public function iniDates(Collection $attributes)
    {
        $this->dates = $attributes->filter(function ($attribute) {
            return $attribute instanceof Attributes\DateTimeAttribute;
        })->map(function ($attribute) {
            return $attribute->getName();
        })->toArray();
    }

    /**
     * Initialize dates by attributes.
     *
     * @param Collection $attributes
     */
    public function iniCasts(Collection $attributes)
    {
        $this->casts = $attributes->mapWithKeys(function ($attribute) {
            return [$attribute->getName() => $attribute];
        })->map(function ($attribute) {
            if ($attribute instanceof Attributes\ObjectAttribute) {
                return 'object';
            }

            if ($attribute instanceof Attributes\ArrayAttribute) {
                return 'array';
            }
            
            if ($attribute instanceof Attributes\BooleanAttribute) {
                return 'boolean';
            }

            if ($attribute instanceof Attributes\DateTimeAttribute) {
                return 'datetime';
            }

            if ($attribute instanceof Attributes\NumberAttribute) {
                return 'float';
            }

            if ($attribute instanceof Attributes\TextAttribute) {
                return 'string';
            }

            return null;
        })->filter(function ($item) {
            return $item !== null;
        })->toArray();
    }
}
