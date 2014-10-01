<?php
/*
 *  Copyright 2013-2014 Christian Grobmeier, Ivan Habunek
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
namespace Cicada;

use Cicada\Routing\Route;
use Cicada\Routing\RouteCollection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class Application extends \Pimple\Container
{
    use RequestProcessorTrait;

    public function __construct()
    {
        parent::__construct();

        $this['router'] = function () {
            return new Routing\Router();
        };

        $this['session'] = function () {
            return new Session();
        };

        $this['collection_factory'] = $this->factory(function () {
            $route = new Route('/');
            return new RouteCollection($route);
        });

        $this['exception_handler'] = function () {
            return new ExceptionHandler();
        };
    }

    public function get($pattern, $callback)
    {
        return $this->query(Route::HTTP_GET, $pattern, $callback);
    }

    public function post($pattern, $callback)
    {
        return $this->query(Route::HTTP_POST, $pattern, $callback);
    }

    public function put($pattern, $callback)
    {
        return $this->query(Route::HTTP_PUT, $pattern, $callback);
    }

    public function delete($pattern, $callback)
    {
        return $this->query(Route::HTTP_DELETE, $pattern, $callback);
    }

    public function head($pattern, $callback)
    {
        return $this->query(Route::HTTP_HEAD, $pattern, $callback);
    }

    public function options($pattern, $callback)
    {
        return $this->query(Route::HTTP_OPTIONS, $pattern, $callback);
    }

    public function query($method, $pattern, $callback)
    {
        $route = new Route($pattern, $callback, $method);
        $this['router']->addRoute($route);

        return $route;
    }

    public function addRouteCollection(RouteCollection $collection)
    {
        $this['router']->addRouteCollection($collection);
    }

    public function exception(callable $callback)
    {
        $this['exception_handler']->add($callback);
    }

    public function run()
    {
        $request = Request::createFromGlobals();

        $callable = [$this['router'], 'route'];

        try {
            // Try to process the request
            $response = $this->processRequest($this, $request, $callable);
        } catch (\Exception $ex) {
            // On failure invoke the error handler
            $response = $this['exception_handler']->handle($ex);
        }

        // If all else fails...
        if ($response === null) {
            $response = new Response("Page failed to render.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->send();
    }
}
