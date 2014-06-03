<?php
/*
 *  Copyright 2013 Christian Grobmeier
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

use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    private $routes = [];

    public function addRoute(Route $route)
    {
        $this->routes[] = $route;
    }

    public function addRouteCollection(RouteCollection $collection)
    {
        $routes = $collection->getRoutes();
        foreach ($routes as $route) {
            $this->routes[] = $route;
        }
    }

    /**
     * Routes the request, and returns a Response.
     *
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function route(Application $app, Request $request)
    {
        $url = $request->getPathInfo();
        $method = $request->getMethod();

        /** @var $route Route */
        foreach ($this->routes as $route) {
            if ($route->getMethod() == $method) {
                $matches = $route->matches($url);
                if ($matches !== false) {
                    return $route->run($app, $request, $matches);
                }
            }
        }

        // Return HTTP 404
        return new Response("Page not found", Response::HTTP_NOT_FOUND);
    }

    /**
     * Returns the collection of routes.
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Returns a route by name.
     *
     * @param  string $name
     * @return Route
     * @throws  \Exception If no route with given name is found.
     * @throws  \InvalidArgumentException If given name is empty.
     */
    public function getRoute($name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException("Route name not provided.");
        }

        // TODO: This is not very efficient, maybe find a better way
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        throw new \Exception("Route \"$name\" not found.");
    }

    /**
     * Returns a route path.
     *
     * @param  string $name   Name of the route.
     * @param  array $params  Route parameters, which are substituted for
     *                        {placeholders}.
     * @return string
     */
    public function getRoutePath($name, $params = [])
    {
        return $this->getRoute($name)->getRealPath($params);
    }
}
