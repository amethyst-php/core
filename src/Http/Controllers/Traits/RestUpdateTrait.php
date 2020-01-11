<?php

namespace Amethyst\Core\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait RestUpdateTrait
{
    /**
     * Display a resource.
     *
     * @param int                      $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $entity = $this->getEntityById($id);

        if (!$entity) {
            return $this->response(null, Response::HTTP_NOT_FOUND);
        }

        $params = $request->only($this->fillable);

        $result = $this->manager->update($entity, $params);

        if (!$result->ok()) {
            return $this->response(['errors' => $result->getSimpleErrors()], Response::HTTP_BAD_REQUEST);
        }

        return $this->response($this->serialize($result->getResource(), $request), Response::HTTP_OK);
    }
}
