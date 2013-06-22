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