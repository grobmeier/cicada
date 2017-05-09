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
namespace Cicada\Tests;

use Cicada\Routing\Route;
use Cicada\Routing\RouteCollection;
use Cicada\Routing\Router;
use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testAddRoute()
    {
        // Create Router, add some Routes
        $router = new Router();
        $routes = [
            new Route('/foo'),
            new Route('/bar'),
            new Route('/baz'),
        ];

        foreach($routes as $route) {
            $router->addRoute($route);
        }

        $this->assertSame($routes, $router->getRoutes());

        // Create a RouteCollection, add some Routes
        $col = new RouteCollection(new Route());

        $col->get('/x', function() {});
        $col->get('/y', function() {});
        $col->get('/z', function() {});

        // Add it to the router
        $router->addRouteCollection($col);

        $allRoutes = array_merge($routes, $col->getRoutes());
        $this->assertSame($allRoutes, $router->getRoutes());

        // Anything added to the collection after adding the collection to the
        // router should not be registered.
        $col->get('/a', function() {});
        $col->get('/b', function() {});
        $col->get('/c', function() {});

        $this->assertSame($allRoutes, $router->getRoutes());
    }

    public function testRoute()
    {
        $router = new Router();

        $router->addRoute(new Route('/foo', function () { return "foo"; }, "GET"));
        $router->addRoute(new Route('/bar', function () { return "bar"; }, "GET"));
        $router->addRoute(new Route('/baz', function () { return "baz"; }, "GET"));


        $app = new Application();

        // This request should match /foo
        $_SERVER["REQUEST_URI"] = "/foo";
        $req = Request::createFromGlobals();

        $response = $router->route($app, $req);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame("foo", $response->getContent());
        $this->assertSame(200, $response->getStatusCode());

        // This request should not match anything
        $_SERVER["REQUEST_URI"] = "/nocigar";
        $req = Request::createFromGlobals();

        $response = $router->route($app, $req);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame("Page not found", $response->getContent());
        $this->assertSame(404, $response->getStatusCode());
    }

    public function testNamedRoutes()
    {
        $router = new Router();

        $r1 = (new Route())->name("r1");
        $r2 = (new Route())->name("r2");
        $r3 = (new Route())->name("r3");

        $router->addRoute($r1);
        $router->addRoute($r2);
        $router->addRoute($r3);

        $this->assertSame($router->getRoute('r1'), $r1);
        $this->assertSame($router->getRoute('r2'), $r2);
        $this->assertSame($router->getRoute('r3'), $r3);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Route "foo" not found.
     */
    public function testNamedRouteNotFound()
    {
        $router = new Router();
        $router->getRoute("foo");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Route name not provided.
     */
    public function testNamedRouteError()
    {
        $router = new Router();
        $router->getRoute(null);
    }

    public function testGetRoutePath()
    {
        $router = new Router();
        $route = (new Route("/foo/{bar}"))->name("route");
        $router->addRoute($route);

        $actual = $router->getRoutePath("route", ["bar" => 1]);
        $expected = "/foo/1";

        $this->assertSame($expected, $actual);
    }
}
