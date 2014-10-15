<?php
/*
 *  Copyright 2013-2014 Christian Grobmeier, Ivan Habunek
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

use Cicada\Application;
use Cicada\ExceptionHandler;
use Cicada\Routing\Route;
use Cicada\Routing\RouteCollection;
use Cicada\Routing\Router;

use Evenement\EventEmitter;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public $indicator;

    public function testRouterAccess()
    {
        $app = new Application();

        $router = $app['router'];
        $this->assertInstanceOf(Router::class, $router);

        $router2 = $app['router'];
        $this->assertInstanceOf(Router::class, $router);

        // Should always return the same instance
        $this->assertSame($router, $router2);
    }

    public function testSessionAccess()
    {
        $app = new Application();

        $session = $app['session'];
        $this->assertInstanceOf(Session::class, $session);

        $session2 = $app['session'];
        $this->assertInstanceOf(Session::class, $session);

        // Should always return the same instance
        $this->assertSame($session, $session2);
    }

    public function testErrorHandlerAccess()
    {
        $app = new Application();

        $exceptionHandler = $app['exception_handler'];
        $this->assertInstanceOf(ExceptionHandler::class, $exceptionHandler);

        $exceptionHandler2 = $app['exception_handler'];
        $this->assertInstanceOf(ExceptionHandler::class, $exceptionHandler);

        // Should always return the same instance
        $this->assertSame($exceptionHandler, $exceptionHandler2);
    }


    public function testRouteCollectionFactory()
    {
        $app = new Application();

        $collection = $app['collection_factory'];
        $this->assertInstanceOf(RouteCollection::class, $collection);

        $collection2 = $app['collection_factory'];
        $this->assertInstanceOf(RouteCollection::class, $collection);

        // Should NOT return same instances
        $this->assertFalse($collection === $collection2);
    }

    public function testAddingRoutes()
    {
        $callback = function() {};

        $app = new Application();
        $app->get('/get', $callback);
        $app->post('/post', $callback);
        $app->put('/put', $callback);
        $app->delete('/delete', $callback);
        $app->head('/head', $callback);

        $routes = $app['router']->getRoutes();

        $this->assertCount(5, $routes);

        $this->assertInstanceOf(Route::class, $routes[0]);
        $this->assertInstanceOf(Route::class, $routes[1]);
        $this->assertInstanceOf(Route::class, $routes[2]);
        $this->assertInstanceOf(Route::class, $routes[3]);
        $this->assertInstanceOf(Route::class, $routes[4]);

        $this->assertSame('/get', $routes[0]->getPath());
        $this->assertSame('/post', $routes[1]->getPath());
        $this->assertSame('/put', $routes[2]->getPath());
        $this->assertSame('/delete', $routes[3]->getPath());
        $this->assertSame('/head', $routes[4]->getPath());

        $this->assertSame($callback, $routes[0]->getCallback());
        $this->assertSame($callback, $routes[1]->getCallback());
        $this->assertSame($callback, $routes[2]->getCallback());
        $this->assertSame($callback, $routes[3]->getCallback());
        $this->assertSame($callback, $routes[4]->getCallback());
    }

    public function testBeforeAfter()
    {
        $this->indicator = [];

        $b1 = function () {
            $this->indicator[] = 'b1';
        };

        $b2 = function () {
            $this->indicator[] = 'b2';
        };

        $a1 = function () {
            $this->indicator[] = 'a1';
        };

        $a2 = function () {
            $this->indicator[] = 'a2';
        };

        $callback = function () {
            $this->indicator[] = 'callback';
            return "Foo";
        };

        $app = new Application();
        $app->get('/', $callback);

        $app->before($b1);
        $app->before($b2);

        $app->after($a1);
        $app->after($a2);

        $_SERVER["REQUEST_URI"] = "/";

        ob_start();
        $app->run();
        $result = ob_get_clean();

        $expected = [
            'b1',
            'b2',
            'callback',
            'a1',
            'a2',
        ];

        $this->assertEquals($expected, $this->indicator);
        $this->assertEquals("Foo", $result);
    }

    public function testEmitter()
    {
        $app = new Application();

        $this->assertInstanceOf(EventEmitter::class, $app['emitter']);
    }

    public function testEmitterMatchEvent()
    {
        $app = new Application();

        // Create a route and a request which matches that route
        $app->get('/', function() {});
        $request = Request::create('http://www.google.com/');

        $app['emitter']->on(Router::EVENT_MATCH, function(Application $app, Request $req, Route $route) {
            $this->indicator = 1;
        });

        $this->indicator = 0;

        $routerCallback = [$app['router'], 'route'];
        $app->processRequest($app, $request, $routerCallback);

        $this->assertSame(1, $this->indicator);
    }

    public function testEmitterNoMatchEvent()
    {
        $app = new Application();

        // Create a route and a request which does NOT match that route
        $app->get('/foo/', function() {});
        $request = Request::create('http://www.google.com/');

        $app['emitter']->on(Router::EVENT_NO_MATCH, function(Application $app, Request $req) {
            $this->indicator = 1;
        });

        $this->indicator = 0;

        $routerCallback = [$app['router'], 'route'];
        $app->processRequest($app, $request, $routerCallback);

        $this->assertSame(1, $this->indicator);
    }
}
