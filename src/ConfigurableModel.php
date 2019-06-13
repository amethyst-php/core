<?php

namespace Railken\Amethyst\Common;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Railken\Bag;
use Railken\Lem\Attributes;
use Railken\EloquentMapper\Mapper;
use Illuminate\Support\Str;

trait ConfigurableModel
{
    use \Imanghafoori\Relativity\DynamicRelations;

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
            static::defineInverseRelationships();
        }

        $vars = static::$internalInitialization;

        $this->table = $vars->get('table');
        $this->fillable = $vars->get('fillable');
        $this->casts = $vars->get('casts');
        $this->dates = $vars->get('dates');
    }

    /**
     * Define automatically the inverse of all the relationships 
     */
    public static function defineInverseRelationships()
    {
        $morphName = static::getStaticMorphName();

        collect(Mapper::relationsCached(static::class))->map(function ($relation, $key) use ($morphName) {
            $related = $relation->model;
            $methodPlural = Str::plural($morphName);

            if ($relation->type === 'BelongsTo' && !method_exists($related, $methodPlural)) {
                $related::has_many($methodPlural, static::class);
            }

            if ($relation->type === 'MorphTo' && !method_exists($related, $methodPlural)) {
                $related::morph_many($methodPlural, static::class, $relation->key);
            }
        });
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

        $classSchema = Config::get($config.'.schema');

        $schema = new $classSchema();

        $attributes = collect(($schema)->getAttributes());

        $vars->set('fillable', static::iniFillable($attributes));
        $vars->set('dates', static::iniDates($attributes));
        $vars->set('casts', static::iniCasts($attributes));

        static::$internalInitialization = $vars;
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

    public static function getStaticMorphName()
    {
        return str_replace('_', '-', (new Inflector())->tableize((new \ReflectionClass(static::class))->getShortName()));
    }
}
