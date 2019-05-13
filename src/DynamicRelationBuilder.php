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

class DynamicRelationBuilder
{

    protected $relation;

    public function __construct(string $type, string $name)
    {
        $this->relation = new Bag();
        $this->relation->set('type', $type);
        $this->relation->set('name', $name);
    }

    public static function make(string $type, string $name)
    {
        return new static($type, $name);
    }

    public function using(string $usingName, string $usingAttribute = null)
    {
        if (!$usingAttribute) {
            $usingAttribute = $usingName;
        }

        $this->relation->set('using.name', $usingName);
        $this->relation->set('using.attribute', $usingAttribute);

        return $this;
    }

    public function to(string $to)
    {
        $this->relation->set('to', $to);

        return $this;
    }

    public function called(string $called)
    {
        $this->relation->set('called', $called);

        return $this;
    }

    public function when(\Closure $when)
    {
        $this->relation->set('when', $when);

        return $this;
    }

    public function getRelation()
    {
        return $this->relation;
    }

    public function foreignPivotKey(string $foreignPivotKey) {
        $this->relation->set('foreignPivotKey', $foreignPivotKey);

        return $this;
    }
    
    public function relatedPivotKey(string $relatedPivotKey) {
        $this->relation->set('relatedPivotKey', $relatedPivotKey);

        return $this;
    }
}
