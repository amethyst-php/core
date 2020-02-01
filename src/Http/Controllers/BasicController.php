<?php
namespace Amethyst\Core\Http\Controllers;

use Amethyst\Core\Http\Controllers\Traits;

class BasicController extends RestManagerController
{
    use Traits\RestCommonTrait;

    public function __construct()
    {
    	$this->middleware(function ($request, $next) {

    		try {
    			$className = app('amethyst')->findManagerByName($request->route('name'));
    		} catch (\Amethyst\Core\Exceptions\DataNotFoundException $e) {
    			abort(404);
    		}

    		$this->manager = new $className();

    		return $next($request);
    	});
    }
}