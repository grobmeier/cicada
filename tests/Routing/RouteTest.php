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
use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    public $indicator;

    public function testAccessors()
    {
        $path = '/foo/bar';
        $callback = function() {};
        $method = Route::HTTP_GET;
        $before = [function() {}];
        $after = [function() {}];
        $name = "foo";

        $route = new Route($path, $callback, $method, $before, $after, $name);

        $this->assertSame($path, $route->getPath());
        $this->assertSame($callback, $route->getCallback());
        $this->assertSame($method, $route->getMethod());
        $this->assertSame($before, $route->getBefore());
        $this->assertSame($after, $route->getAfter());
        $this->assertSame($name, $route->getName());
    }

    public function testNaming()
    {
        $name = "foo";

        $route = new Route();

        $this->assertNull($route->getName());

        $return = $route->name($name);

        $this->assertSame($name, $route->getName());
        $this->assertSame($return, $route);
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

    public function testBeforeAfter()
    {
        $this->indicator = [];

        $b1 = function (Application $app, $x) {
            $this->indicator[] = 'b1';
            $this->indicator[] = func_get_args();
        };

        $b2 = function (Request $req) {
            $this->indicator[] = 'b2';
            $this->indicator[] = func_get_args();
        };

        $b3 = function ($y, $x) {
            $this->indicator[] = 'b3';
            $this->indicator[] = func_get_args();
        };

        $a1 = function (Application $app, $x) {
            $this->indicator[] = 'a1';
            $this->indicator[] = func_get_args();
        };

        $a2 = function (Request $req) {
            $this->indicator[] = 'a2';
            $this->indicator[] = func_get_args();
        };

        $a3 = function ($y, $x) {
            $this->indicator[] = 'a3';
            $this->indicator[] = func_get_args();
        };

        $before = [$b1, $b2, $b3];
        $after = [$a1, $a2, $a3];

        $callback = function () {
            $this->indicator[] = 'callback';
            return "Foo";
        };

        $app = new Application();
        $request = new Request();
        $params = ['x' => 'x_val'];

        $route = new Route('/', $callback, "GET", $before, $after);
        $response = $route->run($app, $request, $params);

        $this->assertInstanceOf(Response::class, $response);

        $expected = [
            'b1', [$app, 'x_val'],
            'b2', [$request],
            'b3', [null, 'x_val'],
            'callback',
            'a1', [$app, 'x_val'],
            'a2', [$request],
            'a3', [null, 'x_val'],
        ];
        $this->assertEquals($expected, $this->indicator);
        $this->assertEquals("Foo", $response->getContent());
    }

    public function testBeforeReturnedValueStopsExection()
    {
        $this->indicator = [];

        $b1 = function () {
            $this->indicator[] = 'b1';
        };

        $b2 = function () {
            $this->indicator[] = 'b2';
            return "Stop! Hammertime.";
        };

        $b3 = function () {
            $this->indicator[] = 'b3';
        };

        $before = [$b1, $b2, $b3];

        $callback = function () {
            $this->indicator[] = 'callback';
            return "Foo";
        };

        $app = new Application();
        $request = new Request();

        $route = new Route('/', $callback, "GET", $before);
        $response = $route->run($app, $request, []);

        $this->assertInstanceOf(Response::class, $response);

        $expected = ['b1', 'b2'];
        $this->assertEquals($expected, $this->indicator);
        $this->assertEquals("Stop! Hammertime.", $response->getContent());
    }

    public function testAfterReturnedValueDoesNotStopExection()
    {
        $this->indicator = [];

        $a1 = function (Application $app, $x) {
            $this->indicator[] = 'a1';
            $this->indicator[] = func_get_args();
        };

        $a2 = function (Request $req) {
            $this->indicator[] = 'a2';
            $this->indicator[] = func_get_args();
            return "Shoud not stop anything";
        };

        $a3 = function ($y, $x) {
            $this->indicator[] = 'a3';
            $this->indicator[] = func_get_args();
        };

        $after = [$a1, $a2, $a3];

        $callback = function () {
            $this->indicator[] = 'callback';
            return "Foo";
        };

        $app = new Application();
        $request = new Request();
        $params = ['x' => 'x_val'];

        $route = new Route('/', $callback, "GET", [], $after);
        $response = $route->run($app, $request, $params);

        $this->assertInstanceOf(Response::class, $response);

        $expected = [
            'callback',
            'a1', [$app, 'x_val'],
            'a2', [$request],
            'a3', [null, 'x_val'],
        ];
        $this->assertEquals($expected, $this->indicator);
        $this->assertEquals("Foo", $response->getContent());
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage The Response content must be a string or object implementing __toString(), "array" given.
     */
    public function testExceptionWhenRouteReturnsCrap()
    {
        $route = new Route('/', function () {
            return []; // Invalid return value for a route
        });

        $app = new Application();
        $request = new Request();
        $route->run($app, $request);
    }

    public function testBuilderSetters()
    {
        $route = new Route();

        // Path
        $this->assertSame('/', $route->getPath());
        $result = $route->path('/foo');
        $this->assertSame('/foo', $route->getPath());
        $this->assertSame($result, $route);

        // Method
        $this->assertNull($route->getMethod());
        $result = $route->method('POST');
        $this->assertSame('POST', $route->getMethod());
        $this->assertSame($result, $route);

        // Before
        $b1 = function () {};
        $b2 = function () {};

        $this->assertEquals([], $route->getBefore());

        $result = $route->before($b1);
        $this->assertEquals([$b1], $route->getBefore());
        $this->assertSame($result, $route);

        $result = $route->before($b2);
        $this->assertEquals([$b1, $b2], $route->getBefore());
        $this->assertSame($result, $route);

        // After
        $a1 = function () {};
        $a2 = function () {};

        $this->assertEquals([], $route->getAfter());

        $result = $route->after($a1);
        $this->assertEquals([$a1], $route->getAfter());
        $this->assertSame($result, $route);

        $result = $route->after($a2);
        $this->assertEquals([$a1, $a2], $route->getAfter());
        $this->assertSame($result, $route);

        // Callback
        $callback = function () {};
        $this->assertNull($route->getCallback());
        $result = $route->callback($callback);
        $this->assertSame($callback, $route->getCallback());
        $this->assertSame($result, $route);

        // Prefix
        $prefix = '/foo';
        $this->assertSame('', $route->getPrefix());
        $result = $route->prefix($prefix);
        $this->assertSame($prefix, $route->getPrefix());
        $this->assertSame($result, $route);

        // Assert
        $this->assertEquals([], $route->getAsserts());

        $result = $route->assert('foo', 'foopattern');
        $this->assertSame(['foo' => 'foopattern'], $route->getAsserts());
        $this->assertSame($result, $route);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unknown HTTP method: XXX
     */
    public function testInvalidMethod()
    {
        $route = new Route();
        $route->method('XXX');
    }

    public function testGetRealPath()
    {
        $route = new Route("/hi/{foo}/ho/{bar}");
        $route->assert('foo', '\\d+');
        $route->assert('bar', '\\d+');

        $actual = $route->getRealPath([
            'foo' => '1',
            'bar' => '2',
        ]);
        $expected = "/hi/1/ho/2";
        $this->assertSame($expected, $actual);
    }

    public function testGetRealPathWithPrefix()
    {
        $route = new Route("/hi/{foo}/ho/{bar}");
        $route->prefix('/prefix')
            ->assert('foo', '\\d+')
            ->assert('bar', '\\d+');

        $actual = $route->getRealPath([
            'foo' => '1',
            'bar' => '2',
        ]);
        $expected = "/prefix/hi/1/ho/2";
        $this->assertSame($expected, $actual);
    }

    public function testGetRealPathWithPrefix2()
    {
        // This time, placeholders in prefix
        $prefix = "/foo/{bar}/baz";

        $route = new Route("/hello/{name}");
        $route->prefix($prefix)
            ->assert('bar', '\\d+')
            ->assert('name', '\\d+');

        $actual = $route->getRealPath([
            'bar' => '1',
            'name' => '2',
        ]);

        $expected = "/foo/1/baz/hello/2";
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Missing parameter "bar"
     */
    public function testGetRealPathMissingParam()
    {
        $route = new Route("/hi/{foo}/ho/{bar}");
        $actual = $route->getRealPath([
            'foo' => '1',
        ]);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Route parameter "foo" must match pattern "\d+", given "foo".
     */
    public function testGetRealPathFailedAssert()
    {
        $route = new Route("/hi/{foo}");
        $route->assert('foo', '\\d+');

        $actual = $route->getRealPath([
            'foo' => 'foo',
        ]);
    }
}
