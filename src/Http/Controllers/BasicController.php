<?php

namespace Amethyst\Core\Http\Controllers;

class BasicController extends RestManagerController
{
    use Traits\RestCommonTrait;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            try {
                $this->manager = app('amethyst')->findManagerByName($request->route('name'));
            } catch (\Amethyst\Core\Exceptions\DataNotFoundException $e) {
                abort(404);
            }

            return $next($request);
        });
    }
}
