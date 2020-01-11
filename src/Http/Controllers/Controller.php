<?php

namespace Amethyst\Core\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Return a new JSON response from the application.
     *
     * @param string|array $data
     * @param int          $status
     * @param array        $headers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @static
     */
    public function response($data = [], $status = Response::HTTP_OK, $headers = [])
    {
        return new JsonResponse($data, $status, $headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param string|array $data
     * @param int          $status
     * @param array        $headers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @static
     */
    public function error($data = [], $status = Response::HTTP_BAD_REQUEST, $headers = [])
    {
        return $this->response($data, $status, $headers);
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param string|array $data
     * @param int          $status
     * @param array        $headers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @static
     */
    public function not_found($data = [], $status = Response::HTTP_NOT_FOUND, $headers = [])
    {
        return $this->response($data, $status, $headers);
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param string|array $data
     * @param int          $status
     * @param array        $headers
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @static
     */
    public function success($data = [], $status = Response::HTTP_OK, $headers = [])
    {
        return $this->response($data, $status, $headers);
    }

    /**
     * Retrieve user.
     *
     * @return mixed
     */
    public function getUser()
    {
        return Auth::user();
    }
}
