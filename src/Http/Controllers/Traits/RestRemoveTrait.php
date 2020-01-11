<?php

namespace Amethyst\Core\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait RestRemoveTrait
{
    /**
     * Display a resource.
     *
     * @param int                      $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function remove($id, Request $request)
    {
        $entity = $this->getEntityById($id);

        if (!$entity) {
            return $this->response(null, Response::HTTP_NOT_FOUND);
        }

        $result = $this->manager->remove($entity);

        if (!$result->ok()) {
            return $this->response(['errors' => $result->getSimpleErrors()], Response::HTTP_BAD_REQUEST);
        }

        return $this->response(null, Response::HTTP_NO_CONTENT);
    }
}
