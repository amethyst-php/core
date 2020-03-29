<?php

namespace Amethyst\Core\Http\Controllers;

use Amethyst\Core\Transformers\BaseTransformer;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use League\Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\TransformerAbstract;
use Railken\Cacheable\CacheableContract;
use Railken\Cacheable\CacheableTrait;
use Railken\EloquentMapper\Scopes\FilterScope;
use Railken\EloquentMapper\With\WithCollection;
use Railken\EloquentMapper\With\WithItem;
use Railken\Lem\Attributes;
use Railken\Lem\Contracts\EntityContract;

abstract class RestManagerController extends Controller implements CacheableContract
{
    use CacheableTrait;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $transformerClass = BaseTransformer::class;

    /**
     * @var \Railken\Lem\Contracts\ManagerContract
     */
    public $manager;

    /**
     * @var array
     */
    public $queryable = [];

    /**
     * @var array
     */
    public $fillable = [];

    protected $startingQuery;

    /**
     * Cache response?
     *
     * @var bool
     */
    protected $cached = false;

    public function __construct()
    {
        $this->initializeManager();
    }

    public function initializeManager()
    {
        $class = $this->class;

        if (!class_exists($class)) {
            throw new \Exception(sprintf("Class %s doesn't exist", $class));
        }

        $this->manager = new $class();
    }

    public function callAction($method, $parameters)
    {
        $request = collect($parameters)->first(function ($item) {
            return $item instanceof Request;
        });

        $this->bootstrap($request);

        return $this->{$method}(...array_values($parameters));
    }

    /**
     * Retrieve resource name.
     *
     * @return string
     */
    public function getResourceName()
    {
        return $this->name !== null ? $this->name : str_replace('_', '-', (new Inflector())->tableize($this->getManager()->getName()));
    }

    /**
     * Return a new instance of Manager.
     *
     * @return \Railken\Lem\Contracts\ManagerContract
     */
    public function getManager()
    {
        return $this->manager;
    }

    public function bootstrap(Request $request)
    {
        if ($this->manager) {
            $this->manager->setAgent($this->getUser());

            $this->initializeQueryable($request);
            $this->initializeFillable($request);
        }
    }

    public function initializeQueryable(Request $request)
    {
        $query = $this->getManager()->getRepository()->getQuery();

        $this->startingQuery = $query;
    }

    public function filterQuery($query, Request $request)
    {
        $include = $request->input('include');

        if (is_string($include) && is_array(json_decode($include, true)) && (json_last_error() == JSON_ERROR_NONE)) {
            $include = json_decode($include);
            $include = new WithCollection(array_map(function ($item) {
                return new WithItem($item->name, $item->query);
            }, $include));
        } else {
            $include = new WithCollection(array_map(function ($item) {
                return new WithItem($item);
            }, explode(',', $include)));
        }

        $scope = new FilterScope();
        $scope->apply($query, strval($request->input('query')), $include);

        $this->queryable = $scope->getKeys();
    }

    public function initializeFillable(Request $request)
    {
        $this->fillable = array_merge($this->fillable, $this->getFillable());
    }

    public function getFillable()
    {
        $fillable = [];

        $attributes = $this->manager->getAttributes()->filter(function ($attribute) {
            return $attribute->getFillable();
        });

        foreach ($attributes as $attribute) {
            if ($attribute instanceof Attributes\BelongsToAttribute) {
                $fillable = array_merge($fillable, [$attribute->getRelationName(), $attribute->getName()]);
            } else {
                $fillable[] = $attribute->getName();
            }
        }

        return $fillable;
    }

    /**
     * Parse the key before using it in the query.
     *
     * @param string $key
     *
     * @return string
     */
    public function parseKey($key)
    {
        $keys = explode('.', $key);

        if (count($keys) === 1) {
            $keys = [$this->getManager()->getRepository()->newEntity()->getTable(), $keys[0]];
        }

        return DB::raw('`'.implode('.', array_slice($keys, 0, -1)).'`.'.$keys[count($keys) - 1]);
    }

    /**
     * Create a new instance for query.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        return $this->getManager()->getRepository()->getQuery();
    }

    /**
     * Create a new instance of fractal transformer.
     *
     * @param \Railken\Lem\Contracts\EntityContract $entity
     * @param \Illuminate\Http\Request              $request
     *
     * @return TransformerAbstract
     */
    public function getFractalTransformer(EntityContract $entity = null, Request $request): TransformerAbstract
    {
        $classTransformer = $this->transformerClass;

        return new $classTransformer($this->getManager(), $request);
    }

    /**
     * Retrieve url base.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    public function getResourceBaseUrl(Request $request): string
    {
        return $request->getSchemeAndHttpHost().Config::get('amethyst.api.http.'.explode('.', Route::getCurrentRoute()->getName())[0].'.router.prefix');
    }

    /**
     * Retrieve fractal manager.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return Fractal\Manager;
     */
    public function getFractalManager(Request $request)
    {
        $manager = new Fractal\Manager();
        $manager->setSerializer(new JsonApiSerializer());

        if ($request->input('include') !== null) {
            $manager->parseIncludes($this->parseIncludes($request->input('include')));
        }

        return $manager;
    }

    public function parseIncludes($include)
    {
        $include = is_array($include) ? $include : explode(',', $include);

        $includes = array_map(function ($element) {
            if (is_string($element) && is_array(json_decode($element, true))) {
                $element = json_decode($element);
            }

            if (is_array($element)) {
                $element = (object) $element;
            }

            if (is_object($element)) {
                return $element->name;
            }

            return $element;
        }, $include);

        return $includes;
    }

    /**
     * Serialize entity.
     *
     * @param \Railken\Lem\Contracts\EntityContract $entity
     * @param \Illuminate\Http\Request              $request
     *
     * @return array
     */
    public function serialize(EntityContract $entity, Request $request)
    {
        $transformer = $this->getFractalTransformer($entity, $request);

        $resource = new Fractal\Resource\Item($entity, $transformer, $this->getResourceName());

        return $this->getFractalManager($request)->createData($resource)->toArray();
    }

    /**
     * Serialize a collection.
     *
     * @param Collection               $collection
     * @param \Illuminate\Http\Request $request
     * @param mixed                    $paginator
     *
     * @return array
     */
    public function serializeCollection(Collection $collection, Request $request, $paginator = null)
    {
        $start = microtime(true);

        $transformer = $this->getFractalTransformer($collection->get(0), $request);

        $resource = new Fractal\Resource\Collection($collection, $transformer, $this->getResourceName());

        if ($paginator) {
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        }

        return $this->getFractalManager($request)->createData($resource)->toArray();
    }

    public function getEntityById($id)
    {
        return $this->getQuery()->where($this->manager->newEntity()->getTable().'.id', $id)->first();
    }
}
