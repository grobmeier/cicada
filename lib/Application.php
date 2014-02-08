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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class Application extends \Pimple
{
    public function __construct()
    {
        $this['router'] = $this->share(function () {
            return new Routing\Router();
        });

        $this['session'] = $this->share(function () {
            return new Session();
        });
    }

    function get($pattern, $callback)
    {
        $route = new Route($pattern, $callback, 'GET');
        $this['router']->addRoute($route);
        return $route;
    }

    function post($pattern, $callback)
    {
        $route = new Route($pattern, $callback, 'POST');
        $this['router']->addRoute($route);
        return $route;
    }

    function put($pattern, $callback)
    {
        $route = new Route($pattern, $callback, 'PUT');
        $this['router']->addRoute($route);
        return $route;
    }

    function delete($pattern, $callback)
    {
        $route = new Route($pattern, $callback, 'DELETE');
        $this['router']->addRoute($route);
        return $route;
    }

    public function run()
    {
        $request = Request::createFromGlobals();
        $response = $this['router']->route($this, $request);
        $response->send();
    }
}
