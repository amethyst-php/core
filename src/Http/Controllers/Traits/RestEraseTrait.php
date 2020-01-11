<?php

namespace Amethyst\Core\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Railken\LaraEye\Filter;
use Railken\Lem\Result;
use Railken\SQ\Exceptions\QuerySyntaxException;
use Symfony\Component\HttpFoundation\Response;

trait RestEraseTrait
{
    /**
     * Display resources.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function erase(Request $request)
    {
        $query = $this->getQuery();

        try {
            $this->filterQuery($query, $request);
        } catch (QuerySyntaxException $e) {
            return $this->error(['code' => 'QUERY_SYNTAX_ERROR', 'message' => 'Syntax error']);
        }
        
        $params = $request->only($this->fillable);

        DB::beginTransaction();

        $result = new Result();
        $counter = 0;
        $query->chunk(100, function ($resources) use ($params, &$result, $counter) {
            foreach ($resources as $resource) {
                $result->addErrors($this->getManager()->remove($resource)->getErrors());
                $counter++;
            }
        });

        if (!$result->ok()) {
            DB::rollBack();

            return $this->response(['errors' => $result->getSimpleErrors()], Response::HTTP_BAD_REQUEST);
        }

        DB::commit();

        return $this->response(['data' => $counter], Response::HTTP_OK);
    }
}
