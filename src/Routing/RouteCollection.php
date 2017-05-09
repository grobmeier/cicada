<?php
/*
 *  Copyright 2013-2015 Christian Grobmeier, Ivan Habunek
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
namespace Cicada\Routing;

class RouteCollection
{
    /**
     * The route on which all routes within the collection are based.
     * @var \Cicada\Routing\Route
     */
    private $baseRoute;

    /**
     * Array of routes within the collection.
     * @var array
     */
    private $routes  = [];

    public function __construct(Route $baseRoute)
    {
        $this->baseRoute = $baseRoute;
    }

    /** Forwards the call to the baseRoute object. */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->baseRoute, $name)) {
            throw new \BadMethodCallException("Method $name does not exist.");
        }

        // Forward the call to the base route
        call_user_func_array([$this->baseRoute, $name], $arguments);

        // Also apply to all already present routes;
        foreach ($this->routes as $route) {
            call_user_func_array([$route, $name], $arguments);
        }

        return $this;
    }

    public function get($path, $callback)
    {
        return $this->query(Route::HTTP_GET, $path, $callback);
    }

    public function post($path, $callback)
    {
        return $this->query(Route::HTTP_POST, $path, $callback);
    }

    public function put($path, $callback)
    {
        return $this->query(Route::HTTP_PUT, $path, $callback);
    }

    public function delete($path, $callback)
    {
        return $this->query(Route::HTTP_DELETE, $path, $callback);
    }

    public function head($path, $callback)
    {
        return $this->query(Route::HTTP_HEAD, $path, $callback);
    }

    public function options($path, $callback)
    {
        return $this->query(Route::HTTP_OPTIONS, $path, $callback);
    }

    public function query($method, $path, $callback)
    {
        $route = clone $this->baseRoute;

        $route->path($path)
            ->callback($callback)
            ->method($method);

        $this->routes[] = $route;

        return $route;
    }

    public function getRoutes()
    {
        return $this->routes;
    }
}
