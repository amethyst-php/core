<?php

namespace Railken\Amethyst\Common;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class Helper
{
    public function getData()
    {
        $return = Collection::make();

        foreach (array_keys(Config::get('amethyst')) as $config) {
            foreach (Config::get('amethyst.'.$config.'.data', []) as $nameData => $data) {
                if (Arr::get($data, 'model')) {
                    $return[] = $data;
                }
            }
        }

        return $return;
    }

    public function getPackages()
    {
        return array_keys(Config::get('amethyst'));
    }

    public function getDataByPackageName($packageName)
    {
        return Collection::make(Config::get('amethyst.'.$packageName.'.data', []))
            ->filter(function ($item) {
                return isset($item['model']);
            })
            ->map(function ($item, $key) {
                return $key;
            });
    }

    public function findPackageNameByData($findName)
    {
        foreach (array_keys(Config::get('amethyst')) as $packageName) {
            foreach (Config::get('amethyst.'.$packageName.'.data', []) as $nameData => $data) {
                if (Arr::get($data, 'model')) {
                    if ($nameData === $findName) {
                        return $packageName;
                    }
                }
            }
        }

        return null;
    }

    public function findDataByModel(string $class)
    {
        return $this->getData()->filter(function ($data) use ($class) {
            return Arr::get($data, 'model') === $class;
        })->first();
    }

    public function findDataByName(string $name)
    {
        return $this->getData()->filter(function ($data) use ($name) {
            return $this->getNameDataByModel(Arr::get($data, 'model')) === $name;
        })->first();
    }

    public function getNameDataByModel(string $class)
    {
        return str_replace('_', '-', (new Inflector())->tableize((new \ReflectionClass($class))->getShortName()));
    }

    public function validMorphRelation(string $data, string $attribute, string $morphable)
    {
        return in_array($morphable, $this->getMorphListable($data, $attribute), true);
    }

    public function getMorphListable(string $data, string $attribute)
    {
        return Config::get($this->getMorphConfig($data, $attribute), []);
    }

    public function getMorphRelationable(string $data, string $attribute)
    {
        return Collection::make(Config::get($this->getMorphConfig($data, $attribute)))->mapWithKeys(function ($item) {
            $data = $this->findDataByName($item);

            return [$item => Arr::get($data, 'manager')];
        })->toArray();
    }

    public function getMorphConfig(string $data, string $attribute)
    {
        $packageName = $this->findPackageNameByData($data);

        return sprintf('amethyst.%s.data.%s.attributes.%s.options', $packageName, $data, $attribute);
    }

    public function pushMorphRelation(string $data, string $attribute, string $morphable, string $alias = null)
    {
        if (!$alias) {
            $alias = $morphable;
        }

        $dataMorphable = $this->findDataByName($morphable);

        if (!$dataMorphable) {
            throw new \Exception(sprintf('Cannot find data from %s', $morphable));
        }

        Relation::morphMap([
            $alias => Arr::get($dataMorphable, 'model'),
        ]);

        Config::push($this->getMorphConfig($data, $attribute), $alias);
    }
    
    public function createMacroMorphRelation($macro, $class, $method, $morphable)
    {
        if (app('amethyst')->validMorphRelation($method, $morphable, $macro->getModel()->getMorphName())) {
            return $macro->getModel()->morphMany($class, $morphable);
        }

        throw new \BadMethodCallException(sprintf("Method %s:%s() doesn't exist", $class, $method));
    }
}
