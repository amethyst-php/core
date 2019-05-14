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
use Illuminate\Database\Eloquent\Relations\MorphToMany;


class Helper implements CacheableContract
{
    use CacheableTrait;

    protected $relationer;
    
    public function __construct()
    {
        $this->config = new Collection();
        $this->relationer = new DynamicRelationResolver();
    }

    public function getData()
    {
        $return = Collection::make();

        foreach (array_keys(Config::get('amethyst')) as $config) {
            foreach (Config::get('amethyst.'.$config.'.data', []) as $nameData => $data) {
                if (Arr::get($data, 'model')) {
                    $return[$nameData] = $data;
                }
            }
        }

        return $return;
    }

    public function getPackages()
    {
        return array_keys(Config::get('amethyst'));
    }

    public function newManagerByModel(string $classModel, AgentContract $agent = null)
    {
        $data = $this->findDataByModelCached($classModel);

        if (!$data) {
            throw new \Exception(sprintf('Missing %s', $classModel));
        }

        $class = Arr::get($data, 'manager');

        return new $class($agent);
    }

    public function findManagerByName(string $name)
    {
        $data = $this->findDataByNameCached($name);

        if (!$data) {
            throw new \Exception(sprintf('Missing %s', $name));
        }
    
        return Arr::get($data, 'manager');
    }

    public function findModelByName(string $name)
    {
        $data = $this->findDataByNameCached($name);

        if (!$data) {
            throw new \Exception(sprintf('Missing %s', $name));
        }
    
        return Arr::get($data, 'model');
    }

    public function findTableByName(string $name)
    {
        $data = $this->findDataByNameCached($name);

        if (!$data) {
            throw new \Exception(sprintf('Missing %s', $name));
        }
    
        return Arr::get($data, 'table');
    }

    public function findModelByTable(string $table)
    {
        $data = $this->findDataByTableNameCached($table);

        if (!$data) {
            throw new \Exception(sprintf('Missing %s', $table));
        }
    
        return Arr::get($data, 'model');
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

    public function findMorphByModel(string $class)
    {
        return (new $class)->getMorphName();
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

    public function findDataByTableName($tableName)
    {
        return $this->getData()->filter(function ($data) use ($tableName) {
            return Arr::get($data, 'table') === $tableName;
        })->first();
    }

    public function getNameDataByModel(string $class)
    {
        return class_exists($class)
            ? str_replace('_', '-', (new Inflector())->tableize((new \ReflectionClass($class))->getShortName()))
            : null;
    }

    public function validMorphRelation(string $data, string $attribute, string $morphable)
    {
        return in_array($morphable, $this->getMorphListable($data, $attribute), true);
    }

    public function getMorphListable(string $data, string $attribute)
    {
        return $this->config->get($this->getMorphConfig($data, $attribute), []);
    }

    public function getMorphRelationable(string $data, string $attribute)
    {
        return Collection::make($this->config->get($this->getMorphConfig($data, $attribute)))->mapWithKeys(function ($item) {
            $data = $this->findDataByNameCached($item);

            return [$item => Arr::get($data, 'manager')];
        })->toArray();
    }

    public function getMorphConfig(string $data, string $attribute)
    {
        $packageName = $this->findPackageNameByDataCached($data);


        return sprintf('amethyst.%s.data.%s.attributes.%s.options', $packageName, $data, $attribute);
    }


    public function createRelation(string $type, string $name)
    {
        return DynamicRelationBuilder::make($type, $name);
    }

    public function resolve(DynamicRelationBuilder $builder)
    {
        return $this->relationer->resolve($builder);
    }

    public function createMorphToMany(string $name)
    {
        return $this->createRelation(MorphToMany::class, $name);
    }

    public function createMacroMorphRelation($macro, $class, $method, $morphable)
    {
        if ($this->validMorphRelation((new $class())->getMorphName(), $morphable, $macro->getModel()->getMorphName())) {
            return $macro->getModel()->morphMany($class, $morphable);
        }

        throw new \BadMethodCallException(sprintf("Method %s:%s() doesn't exist", get_class($macro->getModel()), $method));
    }

    public function createMacroMorphOneRelation($macro, $class, $method, $morphable)
    {
        if ($this->validMorphRelation((new $class())->getMorphName(), $morphable, $macro->getModel()->getMorphName())) {
            return $macro->getModel()->morphOne($class, $morphable);
        }

        throw new \BadMethodCallException(sprintf("Method %s:%s() doesn't exist", get_class($macro->getModel()), $method));
    }

    public function pushMorphRelation(string $data, string $attribute, string $morphable, string $alias = null)
    {
        if ($this->validMorphRelation($data, $attribute, $morphable)) {
            return false;
        }
        
        if (!$alias) {
            $alias = $morphable;
        }

        if (!class_exists($morphable)) {
            $dataMorphable = $this->findDataByNameCached($morphable);

            if (!$dataMorphable) {
                // throw new \Exception(sprintf('Cannot find data from %s', $morphable));

                return null;
            }

            $morphable = Arr::get($dataMorphable, 'model');
        }

        Relation::morphMap([
            $alias => $morphable,
        ]);

        $key = $this->getMorphConfigCached($data, $attribute);

        $this->config->put($key, array_merge($this->config->get($key, []), [$alias]));
    }

    public function parseScope(string $class, array $scopes)
    {
        foreach ($scopes as $k => $scope) {

            $partsColumn = explode('.', $scope['column']);

            if (count($partsColumn) > 1) {

                try {
                    $table = Arr::get($this->findDataByModel($class), 'table');

                    if ($table === $partsColumn[0]) {
                        $partsColumn = array_slice($partsColumn, 1);
                    } else {
                        $partsColumn[0] = $this->getNameDataByModel($this->findModelByTable($partsColumn[0]));
                    }

                    $scopes[$k]['column'] = implode('.', $partsColumn);
                } catch (\Exception $e) {
                    
                }
                
            }
                
        }

        return $scopes;
    }
}
