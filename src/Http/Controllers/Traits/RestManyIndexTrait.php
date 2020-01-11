<?php

namespace Amethyst\Core\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

trait RestManyIndexTrait
{
    /**
     * Display resources.
     *
     * @param mixed   $container_id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index($container_id, Request $request)
    {
        $query = $this->getQuery();

        $container = $this->getManager()->getRepository()->findOneById($container_id);

        if (!$container) {
            return $this->not_found();
        }

        $query = [];

        if ($request->input('query')) {
            $query[] = $request->input('query');
        }

        $relationName = $this->getRelationName($container);

        if ($container->$relationName->count() > 0) {
            $query[] = 'id in ('.$container->$relationName->map(function ($v) {
                return $v->id;
            })->implode(',').')';
        } else {
            $query[] = 'id = 0';
        }

        $query = implode(' and ', $query);

        $request->request->add(['query' => $query]);

        $request = Request::create(route($this->getRelationRoute($container)), 'GET', []);

        return Route::dispatch($request);
    }
}
