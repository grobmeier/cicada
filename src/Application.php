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
namespace Cicada;

use Cicada\Routing\Route;
use Cicada\Routing\RouteCollection;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class Application extends \Pimple
{
    public function __construct()
    {
        parent::__construct();

        $this['router'] = function () {
            return new Routing\Router();
        };

        $this['session'] = function () {
            return new Session();
        };

        $this['collection_factory'] = $this->factory(function() {
            $route = new Route('/');
            return new RouteCollection($route);
        });

        $this['exception_handler'] = function() {
            return new ExceptionHandler();
        };
    }

    public function get($pattern, $callback)
    {
        return $this->route($pattern, $callback, Route::HTTP_GET);
    }

    public function post($pattern, $callback)
    {
        return $this->route($pattern, $callback, Route::HTTP_POST);
    }

    public function put($pattern, $callback)
    {
        return $this->route($pattern, $callback, Route::HTTP_PUT);
    }

    public function delete($pattern, $callback)
    {
        return $this->route($pattern, $callback, Route::HTTP_DELETE);
    }

    public function head($pattern, $callback)
    {
        return $this->route($pattern, $callback, Route::HTTP_HEAD);
    }

    public function route($pattern, $callback, $method)
    {
        $route = new Route($pattern, $callback, $method);
        $this['router']->addRoute($route);

        return $route;
    }

    public function register(RouteCollection $collection)
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

        try {
            // Try to process the request
            $response = $this['router']->route($this, $request);
        } catch (\Exception $ex) {
            // On failure invoke the error handler
            $response = $this['exception_handler']->handle($ex);
        }

        // If all else fails...
        if ($response === null) {
            return new Response("Page failed to render.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->send();
    }
}
