<?php

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
