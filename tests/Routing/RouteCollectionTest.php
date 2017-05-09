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

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testMethods()
    {
        $callback = function () {};
        $path = "/foo";

        $baseRoute = new Route();
        $collection = new RouteCollection($baseRoute);

        $returns = [];

        $returns[] = $collection->get($path, $callback);
        $returns[] = $collection->post($path, $callback);
        $returns[] = $collection->put($path, $callback);
        $returns[] = $collection->delete($path, $callback);
        $returns[] = $collection->head($path, $callback);
        $returns[] = $collection->options($path, $callback);

        foreach ($returns as $return) {
            $this->assertInstanceOf(Route::class, $return);
        }

        $this->assertSame(Route::HTTP_GET, $returns[0]->getMethod());
        $this->assertSame(Route::HTTP_POST, $returns[1]->getMethod());
        $this->assertSame(Route::HTTP_PUT, $returns[2]->getMethod());
        $this->assertSame(Route::HTTP_DELETE, $returns[3]->getMethod());
        $this->assertSame(Route::HTTP_HEAD, $returns[4]->getMethod());
        $this->assertSame(Route::HTTP_OPTIONS, $returns[5]->getMethod());

        $routes = $collection->getRoutes();
        foreach ($routes as $route) {
            $this->assertSame($callback, $route->getCallback());
            $this->assertSame($path, $route->getPath());
            $this->assertSame([], $route->getBefore());
            $this->assertSame([], $route->getAfter());
        }

        // Change path and retest, all routes should be updated
        $path2 = "/bar";
        $collection->path($path2);

        foreach ($routes as $route) {
            $this->assertSame($path2, $route->getPath());
        }
    }

    public function testForwardToExistingRoutes()
    {
        $callback = function () {};
        $before = function () {};
        $after = function () {};

        $path = "/foo";

        // Create a collection
        $baseRoute = new Route();
        $collection = new RouteCollection($baseRoute);

        // Add some routes
        $routes = [];
        $routes[] = $collection->get($path, $callback);
        $routes[] = $collection->get($path, $callback);
        $routes[] = $collection->get($path, $callback);

        // Now add before and after callbacks to the collection
        $collection->before($before);
        $collection->after($after);

        // And check they are present on all routes in collection
        foreach ($routes as $route) {
            $this->assertInternalType('array', $route->getBefore());
            $this->assertCount(1, $route->getBefore());
            $this->assertSame($before, $route->getBefore()[0]);

            $this->assertInternalType('array', $route->getAfter());
            $this->assertCount(1, $route->getAfter());
            $this->assertSame($after, $route->getAfter()[0]);
        }

        // Now add more routes to the collection and check if it has the same
        // before and after
        $routes = [];
        $routes[] = $collection->get($path, $callback);
        $routes[] = $collection->get($path, $callback);
        $routes[] = $collection->get($path, $callback);

        foreach ($routes as $route) {
            $this->assertInternalType('array', $route->getBefore());
            $this->assertCount(1, $route->getBefore());
            $this->assertSame($before, $route->getBefore()[0]);

            $this->assertInternalType('array', $route->getAfter());
            $this->assertCount(1, $route->getAfter());
            $this->assertSame($after, $route->getAfter()[0]);
        }
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage Method foo does not exist.
     */
    public function testInvalidMethod()
    {
        $baseRoute = new Route();
        $collection = new RouteCollection($baseRoute);
        $collection->foo();
    }
}
