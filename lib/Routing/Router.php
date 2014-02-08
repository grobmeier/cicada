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

use Cicada\ActionExecutor;
use Cicada\Responses\EchoResponse;
use Cicada\Session;

use Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router {
    private $routeMap = array();
    private $protectors = array();
    private static $instance;

    private function __construct() {
    }

    public function addRoute(Route $route) {
        $method = $route->getAllowedMethod();
        if (!isset($this->routeMap[$method])) {
            $this->routeMap[$method] = [];
        }

        $this->routeMap[$method][] = $route;
    }

    public function addProtector($pattern, ProtectorInterface $protector) {
        $pattern = '/' . str_replace('/', '\\/', $pattern) . '/';
        array_push($this->protectors, [$pattern, $protector]);
    }

    /**
     * Routes the request, and returns a Response.
     * @param  Request $request
     * @return Response
     */
    public function route(Request $request) {

        $url = $request->getPathInfo();
        $method = $request->getMethod();

        if (isset($this->routeMap[$method])) {
            /** @var $route Route */
            foreach ($this->routeMap[$method] as $route) {

                $pattern = $route->getRoute();
                if (preg_match($pattern, $url, $matches)) {
                    $route->validateMethod($request);
                    $route->validateGet($request);
                    $route->validatePost($request);

                    foreach($this->protectors as list($pattern, $protector)) {
                        if (preg_match($pattern, $url)) {
                            $response = $protector->protect($request);
                            if ($response !== null) {
                                return function() use ($response) {
                                    return $response;
                                };
                            }
                        }
                    }

                    return $this->handle($route, $request, $matches);
                }
            }
        }

        // Return HTTP 404
        return function() {
            return new Response("Route not found", Response::HTTP_NOT_FOUND);
        };
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }

    private function handle(Route $route, Request $request, array $matches) {
        $action = $route->getAction();

        // Keep only named matches, and prepend the request
        $this->cleanMatches($matches);
        array_unshift($matches, $request);

        if (is_string($action)) {
            return $this->handleActionName($action, $matches);
        }

        if ($action instanceof \Closure) {
            return $this->handleClosure($action, $matches);
        }

        // Return HTTP 500
        return function() {
            return new Response("Internal error", Response::HTTP_INTERNAL_SERVER_ERROR);
        };
    }

    /**
     * @param $route
     * @return callable
     */
    private function handleActionName($action, $matches) {

        list($class, $method) = $this->parseActionName($action);

        return function () use ($class, $method, $matches) {
            $object = new $class();
            return call_user_func_array([$object, $method], $matches);
        };
    }

    private function parseActionName($action) {
        $bits = explode('::', $action);
        $count = count($bits);

        // If no function is specified, call execute() by default
        if ($count == 1) {
            $bits[] = 'execute';
        }

        if (count($bits) == 2) {
            return $bits;
        }

        throw new \Exception("Invalid action name: \"$action\"");
    }

    /**
     * @param $route
     * @return callable
     */
    private function handleClosure(callable $action, array $matches) {
        return function () use ($action, $matches) {
            return call_user_func_array($action, $matches);
        };
    }

    /**
     * @param $matches
     */
    private function cleanMatches(&$matches) {
        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                unset($matches[$key]);
            }
        }
    }
}