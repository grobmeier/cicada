<?php

namespace Cicada\Routing;

use Exception;

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
                    return $action();
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