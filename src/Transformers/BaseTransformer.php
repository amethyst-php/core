<?php

namespace Amethyst\Core\Transformers;

use Amethyst\Core\Contracts\TransformerContract;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Railken\EloquentMapper\Contracts\Map as MapContract;
use Railken\Lem\Contracts\EntityContract;
use Railken\Lem\Contracts\ManagerContract;

class BaseTransformer extends TransformerAbstract implements TransformerContract
{
    protected $selectedAttributes = [];
    protected $authorizedAttributes = [];
    /**
     * Manager.
     *
     * @var \Railken\Lem\Contracts\ManagerContract
     */
    protected $manager;

    /**
     * Entity.
     *
     * @var \Railken\Lem\Contracts\EntityContract
     */
    protected $entity;

    /**
     * Http Request.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * @var \Doctrine\Common\Inflector\Inflector
     */
    protected $inflector;

    protected $relationedTransformers = [];

    protected $map;

    /**
     * Create a new instance.
     *
     * @param \Railken\Lem\Contracts\ManagerContract $manager
     * @param \Illuminate\Http\Request               $request
     */
    public function __construct(ManagerContract $manager, Request $request)
    {
        $this->manager = $manager;
        $this->inflector = new Inflector();
        $this->request = $request;

        $this->map = app(MapContract::class);

        $this->availableIncludes = array_keys($this->map->relations($manager->newEntity()));
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        if (preg_match('/^include/', $method)) {
            $method = preg_replace('/^include/', '', $method);

            return $this->resolveInclude(lcfirst($method), $args);
        }

        trigger_error('Call to undefined method '.__CLASS__.'::'.$method.'()', E_USER_ERROR);
    }

    /**
     * Resolve an include using the manager.
     *
     * @param string $relationName
     * @param array  $args
     *
     * @return \League\Fractal\Resource\Item
     */
    public function resolveInclude(string $relationName, array $args)
    {
        $entity = $args[0];

        $relation = $entity->{$relationName};

        if (!$relation) {
            $relationName = $this->inflector->tableize($relationName);
            $relation = $entity->{$relationName};
        }

        if (!$relation) {
            return null;
        }

        if ($relation instanceof Collection) {
            if ($relation->count() === 0) {
                return null;
            }

            $oneRelation = $relation[0];
            $method = 'collection';
        } else {
            $oneRelation = $relation;
            $method = 'item';
        }

        $manager = app('amethyst')->get($this->map->modelToKey($oneRelation));

        if (!$manager) {
            return null;
        }

        $transformer = $this->getTransformerByManager($relationName, $manager);

        $t = $this->$method(
            $relation,
            $transformer,
            str_replace('_', '-', $this->inflector->tableize($manager->getName()))
        );

        return $t;
    }

    public function getTransformerByManager($relationName, $manager)
    {
        if (!isset($this->relationedTransformers[$relationName])) {
            $this->relationedTransformers[$relationName] = new BaseTransformer($manager, $this->request);
        }

        return $this->relationedTransformers[$relationName];
    }

    public function setSelectedAttributes(array $selectedAttributes = [])
    {
        $this->selectedAttributes = $selectedAttributes;

        return $this;
    }

    public function getSelectedAttributes(): array
    {
        return $this->selectedAttributes;
    }

    public function setAuthorizedAttributes(array $authorizedAttributes = [])
    {
        $this->authorizedAttributes = $authorizedAttributes;

        return $this;
    }

    public function getAuthorizedAttributes(): array
    {
        return $this->authorizedAttributes;
    }

    /**
     * Turn this item object into a generic array.
     *
     * @return array
     */
    public function transform(EntityContract $entity)
    {
        $s = $this->manager->getSerializer()->serialize($entity, null)->toArray();

        return $s;
    }
}
