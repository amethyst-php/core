<?php

namespace Amethyst\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Railken\Bag;
use Railken\EloquentMapper\Concerns\Relationer;
use Railken\Lem\Attributes;

trait ConfigurableModel
{
    use Relationer;

    public $internalAttributes;
    public static $internalInitialization = null;

    /**
     * Initialize the model by the configuration.
     *
     * @param string $config
     */
    public function ini(string $config)
    {
        $this->internalAttributes = new Bag();

        if (static::$internalInitialization === null) {
            static::$internalInitialization = new Bag();
            static::internalInitialization($config);
        }

        $vars = static::$internalInitialization;

        $this->table = $vars->get('table');
        $this->fillable = $vars->get('fillable');
        $this->casts = $vars->get('casts');
        $this->dates = $vars->get('dates');
        $this->hidden = $vars->get('hidden');
    }

    /**
     * Initialize the model by the configuration.
     *
     * @param string $config
     */
    public static function internalInitialization(string $config)
    {
        $vars = new Bag();

        $vars->set('table', Config::get($config.'.table'));

        $attributes = static::internalGetAttributes($config);

        $vars->set('fillable', static::iniFillable($attributes));
        $vars->set('dates', static::iniDates($attributes));
        $vars->set('casts', static::iniCasts($attributes));
        $vars->set('hidden', static::iniHidden($attributes));

        static::$internalInitialization = $vars;
    }

    /**
     * Get attributes.
     *
     * @param string $config
     *
     * @return Collection
     */
    public static function internalGetAttributes(string $config)
    {
        $classManager = Config::get($config.'.manager');

        return collect((new $classManager())->getAttributes());
    }

    /**
     * Initialize fillable by attributes.
     *
     * @param Collection $attributes
     *
     * @return array
     */
    public static function iniFillable(Collection $attributes): array
    {
        return $attributes->filter(function ($attribute) {
            return $attribute->getFillable();
        })->map(function ($attribute) {
            return $attribute->getName();
        })->toArray();
    }

    /**
     * Initialize hidden by attributes.
     *
     * @param Collection $attributes
     *
     * @return array
     */
    public static function iniHidden(Collection $attributes): array
    {
        return $attributes->filter(function ($attribute) {
            return $attribute->getHidden();
        })->map(function ($attribute) {
            return $attribute->getName();
        })->toArray();
    }

    /**
     * Initialize dates by attributes.
     *
     * @param Collection $attributes
     *
     * @return array
     */
    public static function iniDates(Collection $attributes): array
    {
        return $attributes->filter(function ($attribute) {
            return $attribute instanceof Attributes\DateTimeAttribute;
        })->map(function ($attribute) {
            return $attribute->getName();
        })->toArray();
    }

    /**
     * Initialize dates by attributes.
     *
     * @param Collection $attributes
     *
     * @return array
     */
    public static function iniCasts(Collection $attributes): array
    {
        return $attributes->mapWithKeys(function ($attribute) {
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

    public function getMorphName()
    {
        return static::getStaticMorphName();
    }
}
