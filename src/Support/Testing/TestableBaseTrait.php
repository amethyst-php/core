<?php

namespace Amethyst\Core\Support\Testing;

use Illuminate\Support\Facades\Route;
use Railken\Lem\Attributes\BelongsToAttribute;
use Symfony\Component\HttpFoundation\Response;

trait TestableBaseTrait
{
    /**
     * Check route.
     *
     * @param string $name
     *
     * @return bool
     */
    public function checkRoute(string $name): bool
    {
        $reflection = new \ReflectionClass($this->getController());

        return $reflection->hasMethod($name);
    }

    /**
     * Retrieve resource url.
     *
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Test common requests.
     */
    public function testSuccessCommon()
    {
        $this->commonTest($this->getRoute());
    }

    /**
     * Retrieve a resource.
     *
     * @param string $routeName
     */
    public function retrieveResource(string $routeName)
    {
        if (!$this->checkRoute('index')) {
            throw new \Exception('Index route should be enabled to retrieve a resource for update, remove and show');
        }

        $response = $this->callAndTest('GET', route($routeName.'.index'), [], Response::HTTP_OK);

        return json_decode($response->getContent())->data[0];
    }

    /**
     * @return array
     */
    public function getFakerParameters()
    {
        return $this->faker::make()->parameters()->toArray();
    }

    /**
     * Test common.
     *
     * @param string $routeName
     */
    public function commonTest(string $routeName)
    {
        if ($this->checkRoute('create')) {
            $response = $this->callAndTest('POST', route($routeName.'.create'), $this->getFakerParameters(), Response::HTTP_CREATED);
        }

        if ($this->checkRoute('index')) {
            $response = $this->callAndTest('GET', route($routeName.'.index'), array_merge($this->getDefaultGetParameters(), []), Response::HTTP_OK);
            $response = $this->callAndTest('GET', route($routeName.'.index'), array_merge($this->getDefaultGetParameters(), ['query' => 'id eq 1']), Response::HTTP_OK);
        }

        if ($this->checkRoute('show')) {
            $resource = $this->retrieveResource($routeName);
            $response = $this->callAndTest('GET', route($routeName.'.show', ['id' => $resource->id]), array_merge($this->getDefaultGetParameters(), []), Response::HTTP_OK);
        }

        if ($this->checkRoute('update')) {
            $resource = $this->retrieveResource($routeName);
            $response = $this->callAndTest('PUT', route($routeName.'.update', ['id' => $resource->id]), $this->getFakerParameters(), Response::HTTP_OK);
        }

        if ($this->checkRoute('remove')) {
            $resource = $this->retrieveResource($routeName);
            $response = $this->callAndTest('DELETE', route($routeName.'.remove', ['id' => $resource->id]), [], Response::HTTP_NO_CONTENT);
        }

        if ($this->checkRoute('store')) {
            $response = $this->callAndTest('POST', route($routeName.'.create'), $this->getFakerParameters(), Response::HTTP_CREATED);
            $resource = json_decode($response->getContent())->data;
            $response = $this->callAndTest('PUT', route($routeName.'.store'), array_merge($this->getFakerParameters(), ['query' => 'id eq "'.$resource->id.'"']), Response::HTTP_OK);
        }

        if ($this->checkRoute('erase')) {
            $response = $this->callAndTest('POST', route($routeName.'.create'), $this->getFakerParameters(), Response::HTTP_CREATED);
            $resource = json_decode($response->getContent())->data;
            $response = $this->callAndTest('DELETE', route($routeName.'.erase'), array_merge($this->getFakerParameters(), ['query' => 'id eq "'.$resource->id.'"']), Response::HTTP_OK);
        }
    }

    public function getController()
    {
        $routeCollection = Route::getRoutes();
        $routes = $routeCollection->getRoutes();
        $name = $this->getRoute();

        $grouped_routes = array_values(array_filter($routes, function ($route) use ($name) {
            $action = $route->getAction();

            return isset($action['as']) && strpos($action['as'], $name) !== false;
        }));

        return explode('@', $grouped_routes[0]->getAction()['controller'])[0];
    }

    /**
     * Retrieve default parameters.
     *
     * @return array
     */
    public function getDefaultGetParameters()
    {
        $controller = $this->app->make($this->getController());

        $attributes = $controller->getManager()->getAttributes()->filter(function ($attribute) {
            return $attribute instanceof BelongsToAttribute;
        })->map(function ($attribute) {
            return $attribute->getRelationName();
        });

        return [
            'include' => $attributes->implode(','),
        ];
    }

    /**
     * Make the call and test it.
     *
     * @param string $method
     * @param string $url
     * @param array  $parameters
     * @param int    $code
     */
    public function callAndTest($method, $url, $parameters, $code)
    {
        $server = $this->transformHeadersToServerVars($this->defaultHeaders);

        $response = $this->call($method, $url, $parameters, [], [], $server);

        $this->printCall($method, $url, $parameters, $response, $code);

        $response->assertStatus($code);

        return $response;
    }

    /**
     * Print the call.
     *
     * @param string $method
     * @param string $url
     * @param array  $parameters
     * @param mixed  $response
     * @param int    $code
     */
    public function printCall($method, $url, $parameters, $response, $code)
    {
        print_r("\n\n----------------------------------------------------------------");
        print_r(sprintf("\n%s %s", $method, $url));
        print_r(sprintf("\n\nParameters Sent:\n%s", json_encode($parameters, JSON_PRETTY_PRINT)));
        print_r(sprintf("\n\nResponse Status Code: %s", $response->getStatusCode()));
        print_r(sprintf("\n\nResponse Body:\n%s\n", json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT)));
    }
}
