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

use Cicada\Invoker;
use Cicada\Application;

use Symfony\Component\HttpFoundation\Request;

function invokerTestFunction(Application $app, Request $request, $foo, $bar)
{
    return func_get_args();
}

class ApplicationSubClass extends Application
{

}

class InvokerTest extends \PHPUnit_Framework_TestCase
{
    private $namedParams = [
        'a' => 'a_val',
        'b' => 'b_val',
        'foo' => 'foo_val',
        'bar' => 'bar_val',
        'x' => 'x_val',
        'y' => 'y_val',
    ];

    public function testInvokeAnonymousFunction()
    {
        $app = new Application();
        $request = new Request();
        $classParams = [$app, $request];

        $function1 = function ($foo, $bar) {
            return func_get_args();
        };
        $function2 = function ($foo, $bar, Request $request) {
            return func_get_args();
        };
        $function3 = function (Application $app, Request $request, $foo, $bar) {
            return func_get_args();
        };
        $function4 = function (Application $bla, Request $tra, $foo, $bar) {
            return func_get_args();
        };
        $function5 = function (Application $bla, Request $tra, $foo, $bar, $nonexistant) {
            return func_get_args();
        };

        $invoker = new Invoker();

        $actual1 = $invoker->invoke($function1, $this->namedParams, $classParams);
        $actual2 = $invoker->invoke($function2, $this->namedParams, $classParams);
        $actual3 = $invoker->invoke($function3, $this->namedParams, $classParams);
        $actual4 = $invoker->invoke($function4, $this->namedParams, $classParams);
        $actual5 = $invoker->invoke($function5, $this->namedParams, $classParams);

        $expected1 = ['foo_val', 'bar_val'];
        $expected2 = ['foo_val', 'bar_val', $request];
        $expected3 = [$app, $request, 'foo_val', 'bar_val'];
        $expected4 = [$app, $request, 'foo_val', 'bar_val'];
        $expected5 = [$app, $request, 'foo_val', 'bar_val', null];

        $this->assertEquals($expected1, $actual1);
        $this->assertEquals($expected2, $actual2);
        $this->assertEquals($expected3, $actual3);
        $this->assertEquals($expected4, $actual4);
        $this->assertEquals($expected5, $actual5);
    }

    public function testInvokeClassMethod()
    {
        $app = new Application();
        $request = new Request();
        $classParams = [$app, $request];

        $invoker = new Invoker();
        $actual1 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute1", $this->namedParams, $classParams);
        $actual2 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute2", $this->namedParams, $classParams);
        $actual3 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute3", $this->namedParams, $classParams);
        $actual4 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute4", $this->namedParams, $classParams);
        $actual5 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute5", $this->namedParams, $classParams);

        $expected1 = ['foo_val', 'bar_val'];
        $expected2 = ['foo_val', 'bar_val', $request];
        $expected3 = [$app, $request, 'foo_val', 'bar_val'];
        $expected4 = [$app, $request, 'foo_val', 'bar_val'];
        $expected5 = [$app, $request, 'foo_val', 'bar_val', null];

        $this->assertEquals($expected1, $actual1);
        $this->assertEquals($expected2, $actual2);
        $this->assertEquals($expected3, $actual3);
        $this->assertEquals($expected4, $actual4);
        $this->assertEquals($expected5, $actual5);
    }

    public function testInvokeClassMethod2()
    {
        $app = new Application();
        $request = new Request();
        $classParams = [$app, $request];

        $invoker = new Invoker();
        $actual1 = $invoker->invoke([InvokerTestAction::class, "execute1"], $this->namedParams, $classParams);
        $actual2 = $invoker->invoke([InvokerTestAction::class, "execute2"], $this->namedParams, $classParams);
        $actual3 = $invoker->invoke([InvokerTestAction::class, "execute3"], $this->namedParams, $classParams);
        $actual4 = $invoker->invoke([InvokerTestAction::class, "execute4"], $this->namedParams, $classParams);
        $actual5 = $invoker->invoke([InvokerTestAction::class, "execute5"], $this->namedParams, $classParams);

        $expected1 = ['foo_val', 'bar_val'];
        $expected2 = ['foo_val', 'bar_val', $request];
        $expected3 = [$app, $request, 'foo_val', 'bar_val'];
        $expected4 = [$app, $request, 'foo_val', 'bar_val'];
        $expected5 = [$app, $request, 'foo_val', 'bar_val', null];

        $this->assertEquals($expected1, $actual1);
        $this->assertEquals($expected2, $actual2);
        $this->assertEquals($expected3, $actual3);
        $this->assertEquals($expected4, $actual4);
        $this->assertEquals($expected5, $actual5);
    }

    public function testInvokeObjectMethod()
    {
        $app = new Application();
        $request = new Request();
        $classParams = [$app, $request];

        $object = new InvokerTestAction;

        $invoker = new Invoker();
        $actual1 = $invoker->invoke([$object, "execute1"], $this->namedParams, $classParams);
        $actual2 = $invoker->invoke([$object, "execute2"], $this->namedParams, $classParams);
        $actual3 = $invoker->invoke([$object, "execute3"], $this->namedParams, $classParams);
        $actual4 = $invoker->invoke([$object, "execute4"], $this->namedParams, $classParams);
        $actual5 = $invoker->invoke([$object, "execute5"], $this->namedParams, $classParams);

        $expected1 = ['foo_val', 'bar_val'];
        $expected2 = ['foo_val', 'bar_val', $request];
        $expected3 = [$app, $request, 'foo_val', 'bar_val'];
        $expected4 = [$app, $request, 'foo_val', 'bar_val'];
        $expected5 = [$app, $request, 'foo_val', 'bar_val', null];

        $this->assertEquals($expected1, $actual1);
        $this->assertEquals($expected2, $actual2);
        $this->assertEquals($expected3, $actual3);
        $this->assertEquals($expected4, $actual4);
        $this->assertEquals($expected5, $actual5);
    }

    public function testInvokeSubClass()
    {
        // Check that a subclass of Application is properly injected
        $app = new ApplicationSubClass();
        $request = new Request();
        $classParams = [$app, $request];

        $invoker = new Invoker();
        $actual1 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute1", $this->namedParams, $classParams);
        $actual2 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute2", $this->namedParams, $classParams);
        $actual3 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute3", $this->namedParams, $classParams);
        $actual4 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute4", $this->namedParams, $classParams);
        $actual5 = $invoker->invoke("Cicada\Tests\InvokerTestAction::execute5", $this->namedParams, $classParams);

        $expected1 = ['foo_val', 'bar_val'];
        $expected2 = ['foo_val', 'bar_val', $request];
        $expected3 = [$app, $request, 'foo_val', 'bar_val'];
        $expected4 = [$app, $request, 'foo_val', 'bar_val'];
        $expected5 = [$app, $request, 'foo_val', 'bar_val', null];

        $this->assertEquals($expected1, $actual1);
        $this->assertEquals($expected2, $actual2);
        $this->assertEquals($expected3, $actual3);
        $this->assertEquals($expected4, $actual4);
        $this->assertEquals($expected5, $actual5);
    }

    public function testInvokeFunction()
    {
        $app = new Application();
        $request = new Request();
        $classParams = [$app, $request];

        $invoker = new Invoker();
        $actual = $invoker->invoke("Cicada\\Tests\\invokerTestFunction", $this->namedParams, $classParams);
        $expected = [$app, $request, 'foo_val', 'bar_val'];

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Given argument is not callable.
     */
    public function testNotCallable()
    {
        $invoker = new Invoker();
        $invoker->invoke(123);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Class HopeThisDoesntExist does not exist.
     */
    public function testClassDoesNotExist()
    {
        $invoker = new Invoker();
        $invoker->invoke("HopeThisDoesntExist::method");
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Method Cicada\Tests\InvokerTest::foo does not exist
     */
    public function testMethodDoesNotExist()
    {
        $invoker = new Invoker();
        $invoker->invoke("Cicada\\Tests\\InvokerTest::foo");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $classParams entries must be objects.
     */
    public function testNonObjectClassParam()
    {
        $function = function () {
        };

        $invoker = new Invoker();
        $invoker->invoke($function, [], ['not_an_object']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $classParams contains multiple objects of the
     *     same class [Symfony\Component\HttpFoundation\Request].
     */
    public function testMultipleClassParamOfSameClass()
    {
        $function = function () {
        };

        $invoker = new Invoker();
        $invoker->invoke($function, [], [new Request(), new Request()]);
    }
}
