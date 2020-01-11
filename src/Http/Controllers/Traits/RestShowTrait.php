<?php

namespace Amethyst\Core\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait RestShowTrait
{
    /**
     * Display a resource.
     *
     * @param mixed                    $id
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $entity = $this->getEntityById($id);

        if (!$entity) {
            return $this->response(null, Response::HTTP_NOT_FOUND);
        }

        return $this->response($this->serialize($entity, $request), Response::HTTP_OK);
    }
}
