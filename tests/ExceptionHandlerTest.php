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

use Cicada\ExceptionHandler;

class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $handler = new ExceptionHandler();
        $callback = function(\Exception $ex) {};

        $handler->add($callback);

        $callbacks = $handler->getCallbacks();
        $this->assertInternalType('array', $callbacks);
        $this->assertCount(1, $callbacks);
        $this->assertSame($callback, reset($callbacks));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid exception callback: Has 0 arguments. Expected exactly 1.
     */
    public function testAddTooFewArguemnts()
    {
        $handler = new ExceptionHandler();
        $callback = function() {};
        $handler->add($callback);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid exception callback: Has 3 arguments. Expected exactly 1.
     */
    public function testAddTooManyArguemnts()
    {
        $handler = new ExceptionHandler();
        $callback = function($x, $y, $z) {};
        $handler->add($callback);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Invalid exception callback: Argument must have a class type hint.
     */
    public function testAddNoTypeHint()
    {
        $handler = new ExceptionHandler();
        $callback = function($x) {};
        $handler->add($callback);
    }

    public function testMultipleCallbacks()
    {
        $handler = new ExceptionHandler();
        $handler->add(function(\InvalidArgumentException $ex) { return 1; });
        $handler->add(function(\Exception $ex) { return 2; });

        $actual = $handler->handle(new \InvalidArgumentException());
        $this->assertSame(1, $actual);

        $actual = $handler->handle(new \Exception());
        $this->assertSame(2, $actual);
    }

    public function testNoMaches()
    {
        $handler = new ExceptionHandler();
        $handler->add(function(\InvalidArgumentException $ex) { return 1; });

        $actual = $handler->handle(new \BadMethodCallException());
        $this->assertNull($actual);
    }
}
