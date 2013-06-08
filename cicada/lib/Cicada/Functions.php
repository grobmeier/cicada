<?php

use Cicada\Routing\Route;
use Cicada\Routing\Router;

function get($pattern, $action) {
    $route = new Route($pattern, $action);
    Router::getInstance()->addRoute($route);
}