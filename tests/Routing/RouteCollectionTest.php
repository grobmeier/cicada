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

namespace Cicada\Tests;

use Cicada\Routing\Route;
use Cicada\Routing\RouteCollection;
use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;

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

        foreach ($returns as $return) {
            $this->assertSame($collection, $return);
        }

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
