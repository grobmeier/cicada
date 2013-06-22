<?php

use Cicada\Auth\UserProvider;
use Cicada\Configuration;
use Cicada\Responses\EchoResponse;
use Cicada\Routing\Protector;
use Cicada\Routing\Route;
use Cicada\Routing\Router;

function protect($pattern, UserProvider $userProvider) {
    $protector = new Protector($pattern, $userProvider);
    Router::getInstance()->addProtector($protector);
    return $protector;
}

function forward($path) {
    return function() use ($path) {
        $echo = new EchoResponse();
        $echo->addHeader("Location: " . $path);
        return $echo;
    };
}

function config($key) {
    $config = Configuration::getInstance();
    return $config->get($key);
}

function get($pattern, $action) {
    $route = new Route($pattern, $action);
    Router::getInstance()->addRoute($route);
    return $route;
}

function readPost($key, $default = "") {
    if (isset ($_POST[$key])) {
        $value = $_POST[$key];
    } else {
        $value = $default;
    }
    return $value;
}