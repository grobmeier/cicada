<?php

namespace Cicada\Routing;

use Exception;
use ReflectionFunction;

class Router {
    private $routeMap = array();
    private static $instance;

    private function __construct() {
    }

    public function addRoute(Route $route) {
        array_push($this->routeMap, $route);
    }

    public function route($url) {
        /** @var $route Route */
        foreach ($this->routeMap as $route) {
            if ($route->matches($url)) {
                return function() use ($route) {
                    $action = $route->getAction();

                    $matches = $route->getMatches();
                    foreach ($matches as $key => $value) {
                        if (is_int($key)) {
                            unset($matches[$key]);
                        }
                    }

                    $function = new ReflectionFunction($action);
                    return $function->invokeArgs($matches);
                };
            }
        }
        throw new Exception("No match for route");
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Router();
        }
        return self::$instance;
    }
}