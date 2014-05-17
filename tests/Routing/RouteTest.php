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

class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testAccessors()
    {
        $path = '/foo/bar';
        $callback = function() {};
        $method = Route::HTTP_GET;
        $before = [function() {}];
        $after = [function() {}];

        $route = new Route($path, $callback, $method, $before, $after);

        $this->assertSame($path, $route->getPath());
        $this->assertSame($callback, $route->getCallback());
        $this->assertSame($method, $route->getMethod());
        $this->assertSame($before, $route->getBefore());
        $this->assertSame($after, $route->getAfter());
    }

    public function testMatching()
    {
        $path = '/foo/bar';

        $route = new Route($path);

        $this->assertSame([], $route->matches('/foo/bar'));
        $this->assertFalse($route->matches('/foo/bar/'));
        $this->assertFalse($route->matches('/foo/ba'));
        $this->assertFalse($route->matches('/foo/barr'));
    }

    public function testMatchingWithVariables()
    {
        $path = '/foo/{x}/bar/{y}/baz';

        $route = new Route($path);

        $expected = ['x' => 1, 'y' => 2];
        $actual = $route->matches('/foo/1/bar/2/baz');
        $this->assertEquals($expected, $actual);
    }
}
