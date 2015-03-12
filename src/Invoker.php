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
namespace Cicada;

class Invoker
{
    /**
     * Invokes a method or anonymous function and returns the result.
     *
     * Parameter injection is done in two ways:
     *
     * 1. `$classParams` array may contain only objects. For each of the objects
     * in the array, if the callable function has a parameter with a type hing
     * to that class, it will be injected.
     *
     * For example, in the following case $req will be populated by the Request
     * from `$classParams`:
     *
     * ```
     * $classParams = [new Request];
     * $callable = new function(Request $req) { };
     * $invoker->invoke($callable, [], $classParams);
     * ```
     *
     * 2. `$namedParams` may contain an associative array containing named
     * parameters. If the callable function has a parameter of the same name as
     * one of the given parameters, it's value will be injected.
     *
     * For example:
     *
     * ```
     * $namedParams = [
     *     'foo' => 1,
     *     'bar' => 2
     * ];
     * $callable = new function($bar, $foo) { };
     * $invoker->invoke($callable, $namedParams);
     * ```
     *
     * In the above example $foo and $bar will be injected from the $namedParams.
     * Note that the order of parameters in the callback function is not
     * significant.
     *
     * If `$namedParams` or `$classParams` contain entries which are not matched
     * to any parameter of the callback function, they will be ignored.
     *
     * If the callback function conatins any parameter which is not matched
     * from `$namedParams` or `$classParams`, it will be set to NULL.
     *
     * @param  string|callable $callable
     * @param  array $namedParams Parameters matched by name.
     * @param  array $classParams Parameters matched by class type hint.
     *
     * @return mixed The return value of the given callback function.
     *
     * @throws \InvalidArgumentException when the callable isn't callable
     * @throws \InvalidArgumentException If `$classParams` contains non-objects.
     * @throws \InvalidArgumentException If `$classParams` contains multiple
     * objects of the same class.
     */
    public function invoke($callable, array $namedParams = [], array $classParams = [])
    {
        $classParams = $this->reindexclassParams($classParams);

        if ($callable instanceof \Closure) {
            return $this->invokeFunction($callable, $namedParams, $classParams);
        }

        if (is_string($callable)) {
            if (strpos($callable, '::') !== false) {
                list($class, $method) = explode('::', $callable);
                return $this->invokeClassMethod($class, $method, $namedParams, $classParams);
            } else {
                return $this->invokeFunction($callable, $namedParams, $classParams);
            }
        }

        if (is_array($callable) && count($callable) == 2 && is_string($callable[1])) {
            if (is_object($callable[0])) {
                return $this->invokeObjectMethod($callable[0], $callable[1], $namedParams, $classParams);
            }

            if (is_string($callable[0])) {
                return $this->invokeClassMethod($callable[0], $callable[1], $namedParams, $classParams);
            }
        }

        throw new \InvalidArgumentException("Given argument is not callable.");
    }

    /**
     * Invokes a method on a class, by creating an instance of the class and
     * then invoking the method.
     */
    private function invokeClassMethod($class, $method, $namedParams, $classParams)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Class $class does not exist.");
        }

        $object = new $class();

        return $this->invokeObjectMethod($object, $method, $namedParams, $classParams);
    }

    /**
     * Invokes a method on an object.
     */
    private function invokeObjectMethod($object, $method, $namedParams, $classParams)
    {
        if (!method_exists($object, $method)) {
            $class = get_class($object);
            throw new \InvalidArgumentException("Method $class::$method does not exist.");
        }

        $reflection = new \ReflectionMethod($object, $method);
        $params = $reflection->getParameters();

        $invokeParams = $this->mapParameters($params, $namedParams, $classParams);

        return call_user_func_array([$object, $method], $invokeParams);
    }

    /** Invokes an anonymous function. */
    private function invokeFunction($function, $namedParams, $classParams)
    {
        $reflection = new \ReflectionFunction($function);
        $params = $reflection->getParameters();

        $invokeParams = $this->mapParameters($params, $namedParams, $classParams);

        return call_user_func_array($function, $invokeParams);
    }

    private function mapParameters(array $params, $namedParams, $classParams)
    {
        // Array of params in order in which they should be passed to the function
        $invokeParams = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $class = $param->getClass();

            // First try to match by class, then by name
            if (isset($class) && isset($classParams[$class->name])) {
                $invokeParams[] = $classParams[$class->name];
            } elseif (isset($namedParams[$name])) {
                $invokeParams[] = $namedParams[$name];
            } else {
                $invokeParams[] = null;
            }
        }

        return $invokeParams;
    }

    /** Reindexes an array of objects by class name. */
    private function reindexClassParams(array $classParams)
    {
        $reindexed = [];

        foreach ($classParams as $param) {
            if (!is_object($param)) {
                throw new \InvalidArgumentException("\$classParams entries must be objects.");
            }

            // Iterate for param class and all parent classes. This way you can
            // inject subclasses as well as the specified class
            for ($class = get_class($param); $class !== false; $class = get_parent_class($class)) {
                if (isset($reindexed[$class])) {
                    throw new \InvalidArgumentException("\$classParams contains multiple objects of the same class [$class].");
                }

                $reindexed[$class] = $param;
            }
        }

        return $reindexed;
    }
}
