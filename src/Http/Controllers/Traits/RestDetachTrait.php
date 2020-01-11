<?php

namespace Amethyst\Core\Http\Controllers\Traits;

use Illuminate\Http\Request;

trait RestDetachTrait
{
    /**
     * Remove a resource.
     *
     * @param string  $container_id
     * @param string  $relation_id
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detach($container_id, $relation_id, Request $request)
    {
        $container = $this->getManager()->getRepository()->findOneBy(['id' => $container_id]);

        if (!$container) {
            return $this->not_found();
        }

        $resource = $this->getRelationManager($container)->getRepository()->findOneBy(['id' => $relation_id]);

        if (!$resource) {
            return $this->not_found();
        }

        $relationName = $this->getRelationName($container);

        $container->$relationName()->detach($resource);

        return $this->success(['message' => 'ok']);
    }
}
