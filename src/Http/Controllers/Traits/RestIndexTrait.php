<?php

namespace Amethyst\Core\Http\Controllers\Traits;

use Amethyst\Core\Support\Exceptions\InvalidSorterFieldException;
use Amethyst\Core\Support\Sorter;
use Illuminate\Http\Request;
use Railken\LaraEye\Filter;
use Railken\SQ\Exceptions\QuerySyntaxException;
use Symfony\Component\HttpFoundation\Response;

trait RestIndexTrait
{
    /**
     * Display resources.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->getQuery();

        try {
            $this->filterQuery($query, $request);
        } catch (QuerySyntaxException $e) {
            return $this->error(['code' => 'QUERY_SYNTAX_ERROR', 'message' => 'Syntax error']);
        }

        if ($request->input('sort')) {
            $sorter = new Sorter();
            $sorter->setKeys($this->queryable);

            try {
                foreach (explode(',', $request->input('sort')) as $sort) {
                    if (substr($sort, 0, 1) === '-') {
                        $sorter->add(substr($sort, 1), 'desc');
                    } else {
                        $sorter->add($sort, 'asc');
                    }
                }
            } catch (InvalidSorterFieldException $e) {
                return $this->response(['errors' => [['code' => 'SORT_INVALID_FIELD', 'message' => 'Invalid field for sorting']]], Response::HTTP_BAD_REQUEST);
            }

            foreach ($sorter->get() as $attribute) {
                $query->orderBy($this->parseKey($attribute->getName()), $attribute->getDirection());
            }
        }

        // $selectable = $this->getSelectedAttributesByRequest($request);

        $query->groupBy($this->getManager()->getRepository()->newEntity()->getTable().".id");
        $result = $query->paginate($request->input('show', 10), ['*'], 'page', $request->input('page'));

        $resources = $result->getCollection();

        return $this->response($this->serializeCollection($resources, $request, $result));
    }
}
