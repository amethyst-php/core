<?php

namespace Amethyst\Core\Http\Controllers;

use Illuminate\Http\Request;

class BasicController extends RestManagerController
{
    use Traits\RestCommonTrait;

    public function __construct()
    {
    }

    public function bootstrap(Request $request)
    {
        try {
            $this->manager = app('amethyst')->findManagerByName($request->route('name'));
        } catch (\Amethyst\Core\Exceptions\DataNotFoundException $e) {
            abort(404);
        }

        parent::bootstrap($request);
    }
}
