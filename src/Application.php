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
namespace Cicada;

use Cicada\Routing\Route;
use Cicada\Routing\RouteCollection;

use Evenement\EventEmitter;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Application extends \Pimple\Container implements HttpKernelInterface
{
    use RequestProcessorTrait;
    use FinishTrait;

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

        $this['emitter'] = function () {
            return new EventEmitter();
        };
    }

    /**
     * Creates a route for a GET request.
     *
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
    public function get($pattern, $callback)
    {
        return $this->query(Route::HTTP_GET, $pattern, $callback);
    }

    /**
     * Creates a route for a POST request.
     *
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
    public function post($pattern, $callback)
    {
        return $this->query(Route::HTTP_POST, $pattern, $callback);
    }

    /**
     * Creates a route for a PUT request.
     *
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
    public function put($pattern, $callback)
    {
        return $this->query(Route::HTTP_PUT, $pattern, $callback);
    }

    /**
     * Creates a route for a DELETE request.
     *
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
    public function delete($pattern, $callback)
    {
        return $this->query(Route::HTTP_DELETE, $pattern, $callback);
    }

    /**
     * Creates a route for a HEAD request.
     *
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
    public function head($pattern, $callback)
    {
        return $this->query(Route::HTTP_HEAD, $pattern, $callback);
    }

    /**
     * Creates a route for a OPTIONS request.
     *
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
    public function options($pattern, $callback)
    {
        return $this->query(Route::HTTP_OPTIONS, $pattern, $callback);
    }

    /**
     * Creates a route for a PATCH request.
     *
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
    public function patch($pattern, $callback)
    {
        return $this->query(Route::HTTP_PATCH, $pattern, $callback);
    }

    /**
     * Creates a route for a request.
     *
     * @param  string   $method   HTTP method to match.
     * @param  string   $pattern  Path pattern which matches the callback.
     * @param  callable $callback Callback function which processes the request.
     *
     * @return Route
     */
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

    /**
     * Adds an exception handler.
     *
     * @param  callable $callback Callback function which handles an exception
     *                            and returns a Response. Exceptions are matched
     *                            by type hints.
     */
    public function exception(callable $callback)
    {
        $this['exception_handler']->add($callback);
    }

    /**
     * Creates the request from globals, handles it and returns the response.
     */
    public function run()
    {
        $request = Request::createFromGlobals();
        $response = $this->handle($request);
        $response->send();

        $this->invokeFinish([], [$this, $request, $response]);
    }

    /**
     * Handles a Request to convert it to a Response.
     *
     * When $catch is true, the implementation must catch all exceptions
     * and do its best to convert them to a Response instance.
     *
     * @param Request $request A Request instance
     * @param int     $type    The type of the request
     *                         (one of HttpKernelInterface::MASTER_REQUEST or HttpKernelInterface::SUB_REQUEST)
     * @param bool    $catch   Whether to catch exceptions or not
     *
     * @return Symfony\Component\HttpFoundation\Response A Response instance
     *
     * @throws \Exception When an Exception occurs during processing
     *
     * @api
     */
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        $callable = [$this['router'], 'route'];

        try {
            // Try to process the request
            $response = $this->processRequest($this, $request, $callable);
        } catch (\Exception $ex) {
            // On failure invoke the error handler
            $response = $this['exception_handler']->handle($ex, $request);
        }

        // If all else fails...
        if ($response === null) {
            $response = new Response("Page failed to render.", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response;
    }
}
