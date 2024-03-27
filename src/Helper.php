<?php

namespace Amethyst\Core;

use Amethyst\Core\Exceptions\DataNotFoundException;
use Doctrine\Inflector\InflectorFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Railken\Cacheable\CacheableContract;
use Railken\Cacheable\CacheableTrait;
use Railken\EloquentMapper\Contracts\Map as MapContract;
use Railken\EloquentMapper\Scopes\FilterScope;
use Railken\Lem\Contracts\AgentContract;
use Railken\Lem\Contracts\ManagerContract;
use Illuminate\Support\Facades\Schema;

class Helper implements CacheableContract
{
    use CacheableTrait;

    protected $config;
    protected $data;
    protected $dataIndexedByModel;
    protected $managers;
    protected $mapper;

    public function __construct(MapContract $mapper)
    {
        $this->mapper = $mapper;
        $this->config = new Collection();
        $this->ini();
    }

    public function ini()
    {
        $this->data = collect();
        $this->dataIndexedByModel = collect();

        $return = Collection::make();

        foreach (array_keys(Config::get('amethyst')) as $config) {
            foreach (Config::get('amethyst.'.$config.'.data', []) as $nameData => $data) {
                if ($manager = Arr::get($data, 'manager')) {
                    $this->addData($nameData, new $manager(null, false));
                }
            }
        }
    }

    public function boot()
    {
        foreach ($this->getData() as $name => $manager) {
            $this->bootData($manager);
        }
    }

    public function addData(string $name, ManagerContract $manager)
    {
        $manager->setName($name);
        $this->data[$name] = $manager;

        // $this->dataIndexedByModel[$class] = $manager;
    }

    public function bootData(ManagerContract $manager)
    {
        $manager->boot();

        Relation::morphMap([
            $manager->getName() => $manager->getEntity(),
        ]);
    }

    public function removeData(string $name)
    {
        $class = $this->findManagerByName($name)->getEntity();

        $this->data->forget($name);
        $this->dataIndexedByModel->forget($class);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDataManagers()
    {
        return $this->data->all();
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
        return $this->findDataByName($name, true);
    }

    public function get(string $name)
    {
        return $this->findManagerByName($name);
    }

    /*

    public function findModelByName(string $name)
    {
        return $this->findDataByName($name, true)->getEntity();
    }

    /*
    public function findTableByName(string $name)
    {
        return $this->findDataByName($name, true)->newEntity()->getTable();
    }*/

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

    /*
     * Return data given model class
     *
     * @param Model $name
     * @param bool $exception
     *
     * @return array
     */
    public function findDataByModel(Model $class, bool $exception = false): ManagerContract
    {
        return $this->get($this->mapper->modelToKey($class));
    }

    /*
     * Return data given name
     *
     * @param string $name
     * @param bool $exception
     *
     * @return array
     */
    public function findDataByName(string $name, bool $exception = false): ManagerContract
    {
        $data = $this->getData()[$name] ?? null;

        if (!$data && $exception) {
            throw new DataNotFoundException(sprintf('Missing `%s`', $name));
        }

        return $data;
    }

    /*
     * Return data given table name
     *
     * @param string $tableName
     * @param bool $exception
     *
     * @return array
     */
    public function findDataByTableName(string $tableName, bool $exception = false): ManagerContract
    {
        $data = $this->getData()->filter(function ($data) use ($tableName) {
            return $data->newEntity()->getTable() === $tableName;
        })->first();

        if (!$data && $exception) {
            throw new DataNotFoundException(sprintf('Missing %s', $tableName));
        }

        return $data;
    }

    /**
     * Return an array containing the name of all data registered.
     *
     * @return array
     */
    public function getDataNames(): array
    {
        return $this->getData()->keys()->all();
    }

    public function findMorphByModel(string $class)
    {
        return $this->tableize($class);
    }

    /**
     * Convert an entity to a table.
     *
     * @param $obj
     *
     * @return string
     */
    public function tableize($obj): string
    {
        return str_replace('_', '-', (InflectorFactory::create()->build())->tableize((new \ReflectionClass($obj))->getShortName()));
    }

    /**
     * Return the name of data given model.
     *
     * @param string $class
     *
     * @return string
     */
    public function getNameDataByModel(string $class): string
    {
        return class_exists($class) ? $this->tableize($class) : null;
    }

    public function filter($query, $str)
    {
        $scope = new FilterScope();
        $scope->apply($query, $str);
    }

    /**
     * Check if all tables has been properly migrated
     *
     * @param string $path
     *
     * @return bool
     */
    public function hasAllTables(string $path): bool
    {
        foreach (array_keys(Config::get($path)) as $name) {
            if (!Schema::hasTable(Config::get(sprintf('amethyst.action.data.%s.table', $name)))) {
                return false;
            }
        }

        return true;
    }
}
