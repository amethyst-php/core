<?php

namespace Railken\Amethyst\Common;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Railken\Lem\Contracts\AgentContract;
use Railken\Cacheable\CacheableTrait;
use Railken\Cacheable\CacheableContract;
use Railken\Lem\Contracts\EntityContract;
use Railken\Bag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class DynamicRelationResolver
{

    protected $builders;

    public function __construct()
    {
        $this->builders = new Collection();
    }

    public function resolve(DynamicRelationBuilder $builder)
    {
        $this->builders->push($builder);

        $relation = $builder->getRelation();
        $resolver = $this;

        if ($relation->get('type') === MorphToMany::class) {

            app('amethyst')->pushMorphRelation($relation->get('using.name'), $relation->get('using.attribute'), $relation->get('name'));

            $method = $relation->get('called');

            \Illuminate\Database\Eloquent\Builder::macro($method, function () use ($resolver, $method): MorphToMany {

                $relation = $resolver->common($this, $resolver, $method);

                $rel = $this->getModel()
                    ->morphToMany(
                        app('amethyst')->findModelByName($relation->get('to')), 
                        $relation->get('using.name'),
                        null,
                        $relation->get('foreignPivotKey', null), 
                        $relation->get('relatedPivotKey', null)
                    )
                    ->using(app('amethyst')->findModelByName($relation->get('using.name')))
                ;

                $when = $relation->get('when');

                if ($when) {
                    $when($rel);
                }

                return $rel;

            });
        }
    }

    public function common($macro, $resolver, $method)
    {
        $model = $macro->getModel();

        $builder = $this->builders->first(function ($builder) use ($model, $method) {
            return $builder->getRelation()->get('name') === $model->getMorphName() && $builder->getRelation()->get('called') === $method;
        });

        if (!$builder) {
            throw new \BadMethodCallException(sprintf("Method %s:%s() doesn't exist", get_class($model), $method));
        }

        return $builder->getRelation();
    }
}
