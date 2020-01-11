<?php

namespace Amethyst\Core;

use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Railken\Cacheable\CacheableContract;
use Railken\Cacheable\CacheableTrait;
use Railken\Lem\Contracts\AgentContract;
use Illuminate\Database\Eloquent\Model;
use Railken\EloquentMapper\Scopes\FilterScope;
use Amethyst\Core\Exceptions\DataNotFoundException;

class Helper implements CacheableContract
{
    use CacheableTrait;

    protected $config;
    protected $data;
    protected $dataIndexedByModel;
    protected $packageByDataName;
    protected $managers;

    public function __construct()
    {
        $this->config = new Collection();
        $this->ini();
    }

    public function ini()
    {
        $this->packageByDataName = collect();

        $this->data = collect();
        $this->managers = collect();

        $return = Collection::make();

        foreach (array_keys(Config::get('amethyst')) as $config) {
            foreach (Config::get('amethyst.'.$config.'.data', []) as $nameData => $data) {
                $class = Arr::get($data, 'model');

                if ($class) {
                    $this->data[$nameData] = $data;
                    $this->packageByDataName[$nameData] = $config;
                    $this->dataIndexedByModel[$class] = $data;
                }
            }
        }
    }

    /*public function filter($query, $str, $entity, $agent, $with = [])
    {
        $filter = new FilterScope();

        $filter->setOnApply(function ($query, $model) use ($agent) {
            $manager = $this->newManagerByModel(get_class($model), $agent);
            $manager->getRepository()->applyScopes($query);
        });

        $filter->apply($query, $entity);

        return $filter;
    }*/

    public function getData()
    {
        return $this->data;
    }

    public function getDataIndexedByModel()
    {
        return $this->dataIndexedByModel;
    }

    public function getPackages()
    {
        return array_keys(Config::get('amethyst'));
    }

    public function newManagerByModel(string $classModel, AgentContract $agent = null)
    {
        $data = $this->findDataByModel($classModel);

        if (!$data) {
            throw new DataNotFoundException(sprintf('Missing %s', $classModel));
        }

        $manager = $this->managers[$data['manager']] ?? app($data['manager']);
    
        $this->managers[$data['manager']] = $manager;
        $manager->setAgent($agent);

        return $manager;
    }

    public function findManagerByName(string $name)
    {
        $data = $this->findDataByName($name);

        if (!$data) {
            throw new DataNotFoundException(sprintf('Missing %s', $name));
        }

        return Arr::get($data, 'manager');
    }

    public function findModelByName(string $name)
    {
        $data = $this->findDataByName($name);

        if (!$data) {
            throw new DataNotFoundException(sprintf('Missing %s', $name));
        }

        return Arr::get($data, 'model');
    }

    public function findTableByName(string $name)
    {
        $data = $this->findDataByName($name);

        if (!$data) {
            throw new DataNotFoundException(sprintf('Missing %s', $name));
        }

        return Arr::get($data, 'table');
    }

    public function findModelByTable(string $table)
    {
        $data = $this->findDataByTableName($table);

        if (!$data) {
            throw new DataNotFoundException(sprintf('Missing table %s', $table));
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
        return Arr::get($this->packageByDataName, $findName);
    }

    public function findMorphByModel(string $class)
    {
        return $this->tableize($class);
    }

    public function findDataByModel(string $class)
    {
        return $this->getDataIndexedByModel()[$class] ?? null;
    }

    public function findDataByName(string $name)
    {
        return $this->getData()[$name] ?? null;
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

    public function putMorphListable(string $data, string $attribute, string $alias)
    {
        $key = $this->getMorphConfig($data, $attribute);
        $this->config->put($key, array_merge($this->config->get($key, []), [$alias]));
    }

    public function getMorphListable(string $data, string $attribute)
    {
        return $this->config->get($this->getMorphConfig($data, $attribute), []);
    }

    public function getMorphRelationable(string $data, string $attribute)
    {
        return Collection::make($this->config->get($this->getMorphConfig($data, $attribute)))->mapWithKeys(function ($item) {
            $data = $this->findDataByName($item);

            return [$item => Arr::get($data, 'manager')];
        })->toArray();
    }

    public function getMorphConfig(string $data, string $attribute)
    {
        $packageName = $this->findPackageNameByData($data);

        return sprintf('amethyst.%s.data.%s.attributes.%s.options', $packageName, $data, $attribute);
    }

    public function parseMorph(string $data, string $attribute, string $morphable, string $method = null)
    {
        if ($this->validMorphRelation($data, $attribute, $morphable)) {
            return [false, false, false, false];
        }

        $dataMorphable = $this->findDataByName($morphable);

        $alias = $morphable;

        $classMorphable = Arr::get($dataMorphable, 'model');

        $model = Arr::get($this->findDataByName($data), 'model');

        if (!$classMorphable || !$model) {
            throw new DataNotFoundException(sprintf("Pushing a dynamic relation with a non existent data %s:%s", $data, $morphable));
        }

        Relation::morphMap([
            $alias => $classMorphable,
        ]);

        $key = $this->getMorphConfig($data, $attribute);

        $this->config->put($key, array_merge($this->config->get($key, []), [$alias]));

        return [
            $classMorphable,
            $method ? $method : Str::plural($data),
            $model,
            $attribute,
        ];
    }

    public function pushMorphRelation(string $data, string $attribute, string $morphable, string $method = null)
    {
        list($class, $method, $model, $attribute) = $this->parseMorph($data, $attribute, $morphable, $method);
        
        if (!$class) {
            return;
        }

        $inflector = new \Doctrine\Common\Inflector\Inflector;
        $method = $inflector->camelize($method);

        return $class::morph_many($method, $model, $attribute);
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

    public function findClasses($directory, $subclass)
    {
        if (!file_exists($directory)) {
            return [];
        }

        $finder = new \Symfony\Component\Finder\Finder();
        $iter = new \hanneskod\classtools\Iterator\ClassIterator($finder->in($directory));

        return array_keys($iter->type($subclass)->where('isInstantiable')->getClassMap());
    }

    public function tableize($obj)
    {
        return str_replace('_', '-', (new Inflector())->tableize((new \ReflectionClass($obj))->getShortName()));
    }
}
