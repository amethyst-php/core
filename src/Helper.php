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

                    Relation::morphMap([
                        $nameData => $class
                    ]);
                }
            }
        }
    }

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

    /**
     * Return package data given data name
     *
     * @param string $name
     *
     * @return array
     */
    public function findPackageNameByData($name): array
    {
        return Arr::get($this->packageByDataName, $name);
    }

    /*
     * Return data given model class
     *
     * @param string $name
     *
     * @return array
     */
    public function findDataByModel(string $class): array
    {
        return $this->getDataIndexedByModel()[$class] ?? null;
    }

    /*
     * Return data given name
     *
     * @param string $name
     *
     * @return array
     */
    public function findDataByName(string $name): ?array
    {
        return $this->getData()[$name] ?? null;
    }

    /*
     * Return data given table name
     *
     * @param string $tableName
     *
     * @return array
     */
    public function findDataByTableName(string $tableName): array
    {
        return $this->getData()->filter(function ($data) use ($tableName) {
            return Arr::get($data, 'table') === $tableName;
        })->first();
    }

    /**
     * Return the name of data given model
     *
     * @param string $class
     *
     * @return string
     */
    public function getNameDataByModel(string $class): string
    {
        return class_exists($class)
            ? str_replace('_', '-', (new Inflector())->tableize((new \ReflectionClass($class))->getShortName()))
            : null;
    }

    /**
     * Return an array containing the name of all data registered
     *
     * @return array
     */
    public function getDataNames(): array
    {
        return $this->getData()->keys()->toArray();
    }

    /**
     * Return an array with the name as the key and the class manager as the item
     *
     * @return array
     */
    public function getDataManagers(): array
    {
        return $this->getData()->map(function ($data, $key) {
            return Arr::get($data, 'manager');
        })->toArray();
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

    public function findMorphByModel(string $class)
    {
        return $this->tableize($class);
    }

    /**
     * Convert an entity to a table
     * 
     * @param $obj
     *
     * @return string
     */
    public function tableize($obj): string
    {
        return str_replace('_', '-', (new Inflector())->tableize((new \ReflectionClass($obj))->getShortName()));
    }
}
